## 002 ? Visual Blueprints

> Render these diagrams in any Mermaid-enabled Markdown viewer (e.g., VS Code with Mermaid preview).

### System Architecture Overview
```mermaid
flowchart TB
    subgraph Client
        A[Browser / Vue SPA]
        B[SSR Edge / SEO Bots]
    end
    subgraph Delivery
        C[Vite Build Output
        (JS/CSS/Assets)]
        D[CDN]
    end
    subgraph Platform
        E[Apache
        (Reverse Proxy)]
        F[PHP-FPM
        + Laravel 12]
        G[Node LTS
        (Build Pipeline)]
    end
    subgraph Core Services
        H[(MySQL 8)]
        I[(Redis)]
        J[(Search Engine
        Meilisearch/Algolia)]
        K[(Object Storage / CDN origin)]
    end
    subgraph External Integrations
        L[Payment Gateway
        (Stripe/Adyen/PayPal)]
        M[Shipping APIs
        (Shippo/EasyPost)]
        N[Analytics & Monitoring
        (GA4, Sentry)]
    end

    A -->|Hydrate| C
    B -->|SSR Cache| E
    C --> D --> A
    E --> F
    F --> H
    F --> I
    F --> J
    F --> K
    F --> L
    F --> M
    F --> N
```

### Catalog & Recommendation Data Flow
```mermaid
sequenceDiagram
    participant Admin
    participant CMS as Admin UI
    participant API as Laravel API
    participant DB as MySQL
    participant Cache as Redis
    participant Reco as Recommendation Job
    participant Client as Vue SPA

    Admin->>CMS: Create/Update Product
    CMS->>API: POST /admin/products
    API->>DB: Persist product, variants, attributes
    API->>Reco: Dispatch related-items job
    Reco->>DB: Fetch candidates
    Reco->>Cache: Store curated related list
    API-->>CMS: Product published response
    Client->>API: GET /products/{id}
    API->>DB: Read product data
    API->>Cache: Fetch related list
    API-->>Client: Product + variants + related items
```

### DevOps & Deployment Pipeline
```mermaid
gantt
    title CI/CD Flow
    dateFormat  YYYY-MM-DD
    axisFormat  %d %b

    section Continuous Integration
    Commit & PR Validation        :active, ci1, 2025-11-03, 1d
    PHP & JS Lint/Test            :ci2, after ci1, 1d
    Build Artefacts (Vite)        :ci3, after ci2, 1d

    section Release
    Staging Deploy & Smoke Tests  :rel1, after ci3, 1d
    Production Approval           :rel2, after rel1, 1d
    Zero-Downtime Deploy          :rel3, after rel2, 1d
    Post-Deploy Monitoring        :rel4, after rel3, 1d
```

### Experience Journey Snapshot
```mermaid
journey
    title Customer Journey ? ?Find & Buy a Laptop?
    section Discovery
      See hero promo on home        :5:Customer
      Use mega menu to select Laptops:4:Customer
    section Evaluation
      Apply filters (brand, GPU)    :4:Customer
      Compare 3 products            :3:Customer
      View PDP with variant selector:5:Customer
    section Purchase
      Add to cart & view related    :4:Customer
      Checkout, pay via Stripe      :5:Customer
    section Post-Purchase
      Receive email & track order   :4:Customer
      Leave review, get loyalty pts :3:Customer
```
