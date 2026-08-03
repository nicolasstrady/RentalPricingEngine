import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';

import {
  CalculatePriceRequest,
  EquipmentCategoryListResponse,
  EquipmentDetails,
  EquipmentListResponse,
  PriceCalculationResponse,
} from './rental-pricing.models';

@Injectable({ providedIn: 'root' })
export class RentalPricingApi {
  private readonly http = inject(HttpClient);

  listCategories(): Observable<EquipmentCategoryListResponse> {
    return this.http.get<EquipmentCategoryListResponse>('/api/equipments/categories');
  }

  listEquipment(category: string): Observable<EquipmentListResponse> {
    return this.http.get<EquipmentListResponse>('/api/equipments', {
      params: { category },
    });
  }

  getEquipment(equipmentId: number): Observable<EquipmentDetails> {
    return this.http.get<EquipmentDetails>(`/api/equipments/${equipmentId}`);
  }

  calculatePrice(
    equipmentId: number,
    period: CalculatePriceRequest,
  ): Observable<PriceCalculationResponse> {
    return this.http.post<PriceCalculationResponse>(
      `/api/equipments/${equipmentId}/pricing/calculate`,
      period,
    );
  }
}
