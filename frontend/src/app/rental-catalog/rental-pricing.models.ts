export type PricingModel = 'tiered' | 'flat_rate';
export type EquipmentCategoryValue =
  'drill' | 'sander' | 'circular_saw' | 'pressure_washer' | 'carpet_cleaner';

export interface EquipmentCategory {
  value: EquipmentCategoryValue;
  label: string;
}

export interface EquipmentCategoryListResponse {
  items: EquipmentCategory[];
}

export interface EquipmentSummary {
  id: number;
  name: string;
  category: EquipmentCategoryValue;
  pricingModel: PricingModel;
}

export interface EquipmentListResponse {
  items: EquipmentSummary[];
  count: number;
}

export interface PricingRate {
  durationInDays: number | null;
  amount: number;
}

export interface EquipmentDetails extends EquipmentSummary {
  rates: PricingRate[];
}

export interface CalculatePriceRequest {
  startDate: string;
  endDate: string;
}

export interface PriceCalculationResponse {
  equipment: EquipmentSummary;
  startDate: string;
  endDate: string;
  durationInDays: number;
  amount: number;
  currency: 'EUR';
}

export type PriceStatus = 'idle' | 'loading' | 'success' | 'error';

export interface EquipmentPresentation {
  imageUrl: string;
  description: string;
  useCase: string;
}

export interface CatalogEquipment {
  equipment: EquipmentSummary;
  presentation: EquipmentPresentation;
  priceStatus: PriceStatus;
  calculation?: PriceCalculationResponse;
}
