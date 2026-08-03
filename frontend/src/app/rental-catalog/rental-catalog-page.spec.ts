import { registerLocaleData } from '@angular/common';
import localeFr from '@angular/common/locales/fr';
import { provideHttpClient } from '@angular/common/http';
import { LOCALE_ID } from '@angular/core';
import { TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { of } from 'rxjs';

import { RentalCatalogPage } from './rental-catalog-page';
import { RentalPricingApi } from './rental-pricing-api';

describe('RentalCatalogPage', () => {
  const api = {
    listCategories: vi.fn(),
    listEquipment: vi.fn(),
    calculatePrice: vi.fn(),
  };

  beforeEach(async () => {
    registerLocaleData(localeFr);
    api.listCategories.mockReturnValue(
      of({
        items: [
          { value: 'drill' as const, label: 'Perceuse' },
          { value: 'sander' as const, label: 'Ponceuse' },
        ],
      }),
    );
    api.listEquipment.mockReturnValue(
      of({
        items: [
          { id: 1, name: 'Perceuse', category: 'drill' as const, pricingModel: 'tiered' as const },
          {
            id: 2,
            name: 'Perceuse à percussion',
            category: 'drill' as const,
            pricingModel: 'tiered' as const,
          },
        ],
        count: 2,
      }),
    );
    api.calculatePrice.mockImplementation((equipmentId: number) =>
      of({
        equipment: {
          id: equipmentId,
          name: equipmentId === 1 ? 'Perceuse' : 'Perceuse à percussion',
          category: 'drill' as const,
          pricingModel: 'tiered' as const,
        },
        startDate: '2026-08-01',
        endDate: '2026-08-05',
        durationInDays: 5,
        amount: equipmentId === 1 ? 60.55 : 79.9,
        currency: 'EUR' as const,
      }),
    );

    await TestBed.configureTestingModule({
      imports: [RentalCatalogPage],
      providers: [
        provideHttpClient(),
        provideRouter([]),
        { provide: LOCALE_ID, useValue: 'fr-FR' },
        { provide: RentalPricingApi, useValue: api },
      ],
    }).compileComponents();
  });

  afterEach(() => vi.clearAllMocks());

  it('loads only categories and does not calculate before a selection', async () => {
    const fixture = TestBed.createComponent(RentalCatalogPage);
    fixture.detectChanges();
    await fixture.whenStable();
    fixture.detectChanges();

    expect(api.listCategories).toHaveBeenCalledOnce();
    expect(api.listEquipment).not.toHaveBeenCalled();
    expect(api.calculatePrice).not.toHaveBeenCalled();
    expect(fixture.nativeElement.querySelector('input[formControlName="startDate"]').value).toBe(
      '',
    );
    expect(fixture.nativeElement.querySelector('input[formControlName="endDate"]').value).toBe('');
    expect(fixture.nativeElement.textContent).toContain('Sélectionnez une grande catégorie');
  });

  it('loads and calculates every equipment from the selected category', async () => {
    const fixture = TestBed.createComponent(RentalCatalogPage);
    fixture.detectChanges();

    const categorySelect: HTMLSelectElement = fixture.nativeElement.querySelector(
      'select[formControlName="category"]',
    );
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
    categorySelect.value = 'drill';
    categorySelect.dispatchEvent(new Event('change'));
    await new Promise((resolve) => setTimeout(resolve, 450));
    fixture.detectChanges();

    expect(api.listEquipment).toHaveBeenCalledWith('drill');
    expect(api.calculatePrice).toHaveBeenCalledTimes(2);
    expect(fixture.nativeElement.querySelectorAll('.equipment-card')).toHaveLength(2);
    expect(fixture.nativeElement.textContent).toContain('60,55');
    expect(fixture.nativeElement.textContent).toContain('79,90');
  });
});
