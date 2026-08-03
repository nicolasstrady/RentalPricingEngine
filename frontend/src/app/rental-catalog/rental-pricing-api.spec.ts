import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { TestBed } from '@angular/core/testing';

import { RentalPricingApi } from './rental-pricing-api';

describe('RentalPricingApi', () => {
  let api: RentalPricingApi;
  let httpTesting: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [RentalPricingApi, provideHttpClient(), provideHttpClientTesting()],
    });

    api = TestBed.inject(RentalPricingApi);
    httpTesting = TestBed.inject(HttpTestingController);
  });

  afterEach(() => httpTesting.verify());

  it('loads equipment categories', () => {
    api.listCategories().subscribe((response) => expect(response.items).toHaveLength(1));

    const request = httpTesting.expectOne('/api/equipments/categories');
    expect(request.request.method).toBe('GET');
    request.flush({ items: [{ value: 'drill', label: 'Perceuse' }] });
  });

  it('loads only equipment from the selected category', () => {
    api.listEquipment('drill').subscribe((response) => expect(response.count).toBe(1));

    const request = httpTesting.expectOne('/api/equipments?category=drill');
    expect(request.request.method).toBe('GET');
    request.flush({
      items: [{ id: 1, name: 'Perceuse', category: 'drill', pricingModel: 'tiered' }],
      count: 1,
    });
  });

  it('loads equipment details with pricing rates', () => {
    api.getEquipment(12).subscribe((response) => expect(response.rates).toHaveLength(3));

    const request = httpTesting.expectOne('/api/equipments/12');
    expect(request.request.method).toBe('GET');
    request.flush({
      id: 12,
      name: 'Perceuse SDS+',
      category: 'drill',
      pricingModel: 'tiered',
      rates: [
        { durationInDays: 1, amount: 32 },
        { durationInDays: 7, amount: 105 },
        { durationInDays: 30, amount: 319 },
      ],
    });
  });

  it('calculates a price for a selected period', () => {
    const period = { startDate: '2026-08-01', endDate: '2026-08-05' };

    api.calculatePrice(1, period).subscribe((response) => expect(response.amount).toBe(60.55));

    const request = httpTesting.expectOne('/api/equipments/1/pricing/calculate');
    expect(request.request.method).toBe('POST');
    expect(request.request.body).toEqual(period);
    request.flush({
      equipment: { id: 1, name: 'Perceuse', category: 'drill', pricingModel: 'tiered' },
      ...period,
      durationInDays: 5,
      amount: 60.55,
      currency: 'EUR',
    });
  });
});
