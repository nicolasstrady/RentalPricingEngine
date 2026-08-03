import { CurrencyPipe } from '@angular/common';
import {
  ChangeDetectionStrategy,
  Component,
  DestroyRef,
  OnInit,
  inject,
  signal,
} from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { ActivatedRoute, RouterLink } from '@angular/router';
import { Subscription, debounceTime } from 'rxjs';

import { presentationFor } from '../rental-catalog/equipment-presentation';
import { RentalPricingApi } from '../rental-catalog/rental-pricing-api';
import {
  CalculatePriceRequest,
  EquipmentCategoryValue,
  EquipmentDetails,
  EquipmentPresentation,
  PriceCalculationResponse,
  PricingModel,
} from '../rental-catalog/rental-pricing.models';

const categoryLabels: Record<EquipmentCategoryValue, string> = {
  drill: 'Perceuse',
  sander: 'Ponceuse',
  circular_saw: 'Scie circulaire',
  pressure_washer: 'Nettoyeur haute pression',
  carpet_cleaner: 'Shampouineuse',
};

@Component({
  selector: 'app-equipment-details-page',
  imports: [CurrencyPipe, ReactiveFormsModule, RouterLink],
  templateUrl: './equipment-details-page.html',
  styleUrl: './equipment-details-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class EquipmentDetailsPage implements OnInit {
  private readonly api = inject(RentalPricingApi);
  private readonly route = inject(ActivatedRoute);
  private readonly destroyRef = inject(DestroyRef);
  private readonly formBuilder = inject(FormBuilder);
  private calculationSubscription?: Subscription;

  protected readonly dateForm = this.formBuilder.nonNullable.group({
    startDate: ['', Validators.required],
    endDate: ['', Validators.required],
  });
  protected readonly equipment = signal<EquipmentDetails | null>(null);
  protected readonly presentation = signal<EquipmentPresentation | null>(null);
  protected readonly loading = signal(true);
  protected readonly error = signal(false);
  protected readonly calculation = signal<PriceCalculationResponse | null>(null);
  protected readonly calculationLoading = signal(false);
  protected readonly calculationError = signal<string | null>(null);

  ngOnInit(): void {
    this.dateForm.valueChanges
      .pipe(debounceTime(400), takeUntilDestroyed(this.destroyRef))
      .subscribe(() => this.calculatePrice());

    this.loadEquipment();
  }

  protected loadEquipment(): void {
    const equipmentId = Number(this.route.snapshot.paramMap.get('id'));

    if (!Number.isInteger(equipmentId) || equipmentId <= 0) {
      this.loading.set(false);
      this.error.set(true);
      return;
    }

    this.loading.set(true);
    this.error.set(false);

    this.api
      .getEquipment(equipmentId)
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: (equipment) => {
          this.equipment.set(equipment);
          this.presentation.set(presentationFor(equipment.name));
          this.loading.set(false);
        },
        error: () => {
          this.loading.set(false);
          this.error.set(true);
        },
      });
  }

  protected categoryLabel(category: EquipmentCategoryValue): string {
    return categoryLabels[category];
  }

  protected pricingModelLabel(pricingModel: PricingModel): string {
    return pricingModel === 'flat_rate' ? 'Forfait unique' : 'Tarification dégressive';
  }

  protected rateDurationLabel(durationInDays: number | null): string {
    if (durationInDays === null) {
      return 'Par location';
    }

    return durationInDays === 1 ? '1 jour' : `${durationInDays} jours`;
  }

  private calculatePrice(): void {
    this.calculationSubscription?.unsubscribe();
    const equipment = this.equipment();
    const period = this.validPeriod();

    if (!equipment || !period) {
      return;
    }

    this.calculation.set(null);
    this.calculationLoading.set(true);

    this.calculationSubscription = this.api
      .calculatePrice(equipment.id, period)
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: (calculation) => {
          this.calculation.set(calculation);
          this.calculationLoading.set(false);
        },
        error: () => {
          this.calculationLoading.set(false);
          this.calculationError.set('Le prix n’a pas pu être calculé pour cette période.');
        },
      });
  }

  private validPeriod(): CalculatePriceRequest | null {
    const { startDate, endDate } = this.dateForm.getRawValue();

    this.calculation.set(null);
    this.calculationLoading.set(false);

    if (!startDate || !endDate) {
      this.calculationError.set(null);
      return null;
    }

    const parsedStartDate = new Date(`${startDate}T00:00:00`);
    const parsedEndDate = new Date(`${endDate}T00:00:00`);

    if (parsedEndDate < parsedStartDate) {
      this.calculationError.set(
        'La date de fin doit être postérieure ou égale à la date de début.',
      );
      return null;
    }

    this.calculationError.set(null);
    return { startDate, endDate };
  }
}
