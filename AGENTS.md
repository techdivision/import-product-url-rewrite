# AGENTS.md - import-product-url-rewrite

## Zweck & Verantwortung

Das `import-product-url-rewrite` Modul bietet **Product URL Rewrite Import-Funktionalität**. Es ist ein **Tier 5 Modul** und erweitert `import-product`.

**Hauptverantwortung:**
- URL Rewrite Import
- URL Path Management
- Product-Category URL Management
- Repository Pattern für URL Rewrites
- Service Layer für URL-Verarbeitung
- Observer Pattern für URL-Hooks

## Architektur & Design Patterns

### Kern-Klassen
- **UrlRewriteRepository**: Persistierung von URL Rewrites
- **UrlPathRepository**: Persistierung von URL Paths
- **UrlRewriteProcessor**: Service Layer
- **UrlRewriteObserver**: Observer für Hooks

### Verwendete Patterns
- **Observer Pattern**: Für URL-Hooks
- **Repository Pattern**: Für Daten-Persistierung
- **Service Layer**: Für Business Logic

## Abhängigkeiten

### Externe Pakete
- **Keine**

### TechDivision Dependencies
- **import-product** ^26.2 - Product Importer

### Abhängig von diesem Modul (1 Reverse Dependency)
- **import-cli-simple** - Master CLI

## Wichtige Entry Points

### Repository Klassen
```php
// URL Rewrite Repository
UrlRewriteRepository::create($row): void
UrlRewriteRepository::findByProductId($productId): array

// URL Path Repository
UrlPathRepository::create($row): void
```

## Events & Extension Points

**Keine Events** - Tier 5 Importer-Modul

## Hints für KI-Agenten

### Wichtig zu verstehen
1. **Tier 5 Modul**: Erweitert Product Importer
2. **URL-fokussiert**: Spezialisiert auf URL Rewrites
3. **Observer Pattern**: Für Hooks
4. **Repository Pattern**: Für Persistierung

## Bekannte Einschränkungen

- **URL-Only**: Keine anderen Features
- **Abhängig von Products**: Erfordert Products zu existieren

## Zusammenfassung

`import-product-url-rewrite` ist ein **Tier 5 Modul**, das Product URL Rewrite Import-Funktionalität bietet. Es erweitert den Product Importer mit spezialisierter Funktionalität für URL Rewrites.

**Für Agenten:** Verstehe dieses Modul als **URL Rewrite Importer** mit Observer und Repository Pattern.
