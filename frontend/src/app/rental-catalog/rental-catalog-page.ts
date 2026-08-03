import { CurrencyPipe } from '@angular/common';
import {
  ChangeDetectionStrategy,
  Component,
  DestroyRef,
  OnInit,
  computed,
  inject,
  signal,
} from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterLink } from '@angular/router';
import { Subscription, catchError, debounceTime, forkJoin, map, of } from 'rxjs';

import { presentationFor } from './equipment-presentation';
import { RentalPricingApi } from './rental-pricing-api';
import {
  CalculatePriceRequest,
  CatalogEquipment,
  EquipmentCategory,
  PriceCalculationResponse,
} from './rental-pricing.models';

interface CalculationOutcome {
  equipmentId: number;
  calculation?: PriceCalculationResponse;
}

@Component({
  selector: 'app-rental-catalog-page',
  imports: [CurrencyPipe, ReactiveFormsModule, RouterLink],
  templateUrl: './rental-catalog-page.html',
  styleUrl: './rental-catalog-page.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class RentalCatalogPage implements OnInit {
  private readonly api = inject(RentalPricingApi);
  private readonly destroyRef = inject(DestroyRef);
  private readonly formBuilder = inject(FormBuilder);
  private catalogSubscription?: Subscription;
  private calculationSubscription?: Subscription;

  protected readonly periodForm = this.formBuilder.nonNullable.group({
    category: ['', Validators.required],
    startDate: ['', Validators.required],
    endDate: ['', Validators.required],
  });
  protected readonly categories = signal<EquipmentCategory[]>([]);
  protected readonly equipment = signal<CatalogEquipment[]>([]);
  protected readonly catalogLoading = signal(true);
  protected readonly catalogError = signal(false);
  protected readonly periodError = signal<string | null>(null);
  protected readonly selectedCategory = signal<string | null>(null);
  protected readonly equipmentCount = computed(() => this.equipment().length);
  protected readonly selectedCategoryLabel = computed(
    () =>
      this.categories().find((category) => category.value === this.selectedCategory())?.label ??
      null,
  );

  ngOnInit(): void {
    this.periodForm.valueChanges
      .pipe(debounceTime(400), takeUntilDestroyed(this.destroyRef))
      .subscribe(() => {
        const category = this.periodForm.controls.category.value;

        if (category !== this.selectedCategory()) {
          this.selectedCategory.set(category || null);
          this.periodError.set(null);
          this.calculationSubscription?.unsubscribe();

          if (category) {
            this.loadEquipmentByCategory(category);
          } else {
            this.catalogLoading.set(false);
            this.equipment.set([]);
          }

          return;
        }

        if (category) {
          this.calculateCategoryPrices();
        }
      });

    this.loadCategories();
  }

  protected retryCatalog(): void {
    const category = this.selectedCategory();

    if (category) {
      this.loadEquipmentByCategory(category);
    } else {
      this.loadCategories();
    }
  }

  protected calculateCategoryPrices(): void {
    this.calculationSubscription?.unsubscribe();
    const period = this.validPeriod();

    if (!period || this.equipment().length === 0) {
      return;
    }

    this.equipment.update((items) =>
      items.map((item) => ({ ...item, priceStatus: 'loading', calculation: undefined })),
    );

    const requests = this.equipment().map(({ equipment }) =>
      this.api.calculatePrice(equipment.id, period).pipe(
        map((calculation): CalculationOutcome => ({
          equipmentId: equipment.id,
          calculation,
        })),
        catchError(() => of<CalculationOutcome>({ equipmentId: equipment.id })),
      ),
    );

    this.calculationSubscription = forkJoin(requests)
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe((outcomes) => {
        const calculations = new Map(
          outcomes.map((outcome) => [outcome.equipmentId, outcome.calculation]),
        );

        this.equipment.update((items) =>
          items.map((item) => {
            const calculation = calculations.get(item.equipment.id);

            return {
              ...item,
              calculation,
              priceStatus: calculation ? 'success' : 'error',
            };
          }),
        );
      });
  }

  protected pricingModelLabel(pricingModel: string): string {
    return pricingModel === 'flat_rate' ? 'Forfait unique' : 'Tarif dégressif';
  }

  protected durationLabel(durationInDays: number): string {
    return durationInDays === 1 ? '1 jour' : `${durationInDays} jours`;
  }

  private loadCategories(): void {
    this.catalogSubscription?.unsubscribe();
    this.catalogLoading.set(true);
    this.catalogError.set(false);

    this.catalogSubscription = this.api
      .listCategories()
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: (response) => {
          this.categories.set(response.items);
          this.catalogLoading.set(false);
        },
        error: () => {
          this.catalogLoading.set(false);
          this.catalogError.set(true);
        },
      });
  }

  private loadEquipmentByCategory(category: string): void {
    this.catalogSubscription?.unsubscribe();
    this.catalogLoading.set(true);
    this.catalogError.set(false);
    this.equipment.set([]);

    this.catalogSubscription = this.api
      .listEquipment(category)
      .pipe(takeUntilDestroyed(this.destroyRef))
      .subscribe({
        next: (response) => {
          this.equipment.set(
            response.items.map((equipment) => ({
              equipment,
              presentation: presentationFor(equipment.name),
              priceStatus: 'idle',
            })),
          );
          this.catalogLoading.set(false);
          this.calculateCategoryPrices();
        },
        error: () => {
          this.catalogLoading.set(false);
          this.catalogError.set(true);
        },
      });
  }

  private validPeriod(): CalculatePriceRequest | null {
    const { startDate, endDate } = this.periodForm.getRawValue();

    if (!startDate || !endDate) {
      this.periodError.set('Renseignez une date de début et une date de fin.');
      this.resetCalculations();
      return null;
    }

    const parsedStartDate = new Date(`${startDate}T00:00:00`);
    const parsedEndDate = new Date(`${endDate}T00:00:00`);

    if (Number.isNaN(parsedStartDate.getTime()) || Number.isNaN(parsedEndDate.getTime())) {
      this.periodError.set('Les dates renseignées ne sont pas valides.');
      return null;
    }

    if (parsedEndDate < parsedStartDate) {
      this.periodError.set('La date de fin doit être postérieure ou égale à la date de début.');
      this.resetCalculations();
      return null;
    }

    this.periodError.set(null);
    return { startDate, endDate };
  }

  private resetCalculations(): void {
    this.equipment.update((items) =>
      items.map((item) => ({ ...item, priceStatus: 'idle', calculation: undefined })),
    );
  }
}
