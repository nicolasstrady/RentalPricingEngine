import { convertToParamMap } from '@angular/router';
import { ActivatedRoute, provideRouter } from '@angular/router';
import { TestBed } from '@angular/core/testing';
import { of } from 'rxjs';

import { RentalPricingApi } from '../rental-catalog/rental-pricing-api';
import { EquipmentDetailsPage } from './equipment-details-page';

describe('EquipmentDetailsPage', () => {
  const api = {
    calculatePrice: vi.fn(),
    getEquipment: vi.fn(),
  };

  beforeEach(async () => {
    api.getEquipment.mockReturnValue(
      of({
        id: 12,
        name: 'Perceuse SDS+',
        category: 'drill' as const,
        pricingModel: 'tiered' as const,
        rates: [
          { durationInDays: 1, amount: 32 },
          { durationInDays: 7, amount: 105 },
          { durationInDays: 30, amount: 319 },
        ],
      }),
    );
    api.calculatePrice.mockReturnValue(
      of({
        equipment: {
          id: 12,
          name: 'Perceuse SDS+',
          category: 'drill' as const,
          pricingModel: 'tiered' as const,
        },
        startDate: '2026-08-01',
        endDate: '2026-08-05',
        durationInDays: 5,
        amount: 60.55,
        currency: 'EUR' as const,
      }),
    );

    await TestBed.configureTestingModule({
      imports: [EquipmentDetailsPage],
      providers: [
        provideRouter([]),
        { provide: RentalPricingApi, useValue: api },
        {
          provide: ActivatedRoute,
          useValue: { snapshot: { paramMap: convertToParamMap({ id: '12' }) } },
        },
      ],
    }).compileComponents();
  });

  afterEach(() => vi.clearAllMocks());

  it('loads and displays the equipment pricing rates', () => {
    const fixture = TestBed.createComponent(EquipmentDetailsPage);
    fixture.detectChanges();

    expect(api.getEquipment).toHaveBeenCalledWith(12);
    expect(fixture.nativeElement.textContent).toContain('Perceuse SDS+');
    expect(fixture.nativeElement.textContent).toContain('1 jour');
    expect(fixture.nativeElement.textContent).toContain('7 jours');
    expect(fixture.nativeElement.querySelectorAll('.rate-card')).toHaveLength(3);
    expect(api.calculatePrice).not.toHaveBeenCalled();
  });

  it('calculates the rental amount when both dates are selected', async () => {
    const fixture = TestBed.createComponent(EquipmentDetailsPage);
    fixture.detectChanges();

    const startDateInput: HTMLInputElement = fixture.nativeElement.querySelector(
      'input[formControlName="startDate"]',
    );
    const endDateInput: HTMLInputElement = fixture.nativeElement.querySelector(
      'input[formControlName="endDate"]',
    );
    startDateInput.value = '2026-08-01';
    startDateInput.dispatchEvent(new Event('input'));
    endDateInput.value = '2026-08-05';
    endDateInput.dispatchEvent(new Event('input'));

    await new Promise((resolve) => setTimeout(resolve, 450));
    fixture.detectChanges();

    expect(api.calculatePrice).toHaveBeenCalledWith(12, {
      startDate: '2026-08-01',
      endDate: '2026-08-05',
    });
    expect(fixture.nativeElement.textContent).toContain('Montant pour 5 jours');
    expect(fixture.nativeElement.textContent).toMatch(/60[,.]55/);
  });
});
