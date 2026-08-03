import { Routes } from '@angular/router';

export const routes: Routes = [
  {
    path: 'equipments/:id',
    loadComponent: () =>
      import('./equipment-details/equipment-details-page').then(
        (component) => component.EquipmentDetailsPage,
      ),
  },
  {
    path: '',
    loadComponent: () =>
      import('./rental-catalog/rental-catalog-page').then(
        (component) => component.RentalCatalogPage,
      ),
  },
  { path: '**', redirectTo: '' },
];
