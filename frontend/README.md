# Rental Pricing Frontend

Angular 21 standalone application used to demonstrate the Symfony rental pricing API.

The page first loads the lightweight category list. It does not load equipment or request any price
before the user selects a category. It then fetches and calculates prices only for equipment in that
category and the global rental period. Pricing rules remain exclusively in the Symfony back-end.

## Run with Docker

From the repository root:

```bash
make rebuild
make database
```

Open <http://localhost:4200>. The Angular development server proxies `/api` to the `php` service.

## Run locally

Start Symfony on port `8000`, then run:

```bash
npm install
npm start
```

The local proxy forwards `/api` to <http://localhost:8000>.

## Checks

```bash
npm run format:check
npm run test:ci
npm run build
```

Tests use Vitest. The production build also performs Angular template and TypeScript checks.

## Feature structure

Application code is grouped under `src/app/rental-catalog/`:

- `rental-pricing.models.ts`: API contracts and view-state types;
- `rental-pricing-api.ts`: typed HTTP access to Symfony;
- `rental-catalog-page.ts`: form and request orchestration;
- `equipment-presentation.ts`: front-end-only descriptions and local image mapping.

The lazy-loaded `src/app/equipment-details/` feature displays an equipment and all pricing rates
returned by `GET /api/equipments/{id}`.

See [ASSETS.md](./ASSETS.md) for the catalogue image provenance.
