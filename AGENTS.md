# AGENTS.md - import-product-url-rewrite

## Zweck & Verantwortung

Das `import-product-url-rewrite` Modul bietet **Product URL Rewrite Import-Funktionalität** für SEO-freundliche URLs und Request Path Management. Es ist ein **Tier 5 Modul** in der Import-Architektur und erweitert das `import-product` Modul mit spezialisierten Funktionen für URL-Verwaltung.

**Hauptverantwortung:**
- Product URL Rewrite Import
- SEO-freundliche URL Management
- Request Path to Target Path Mapping
- Redirect Type Handling (301, 302)
- Store-spezifische URL-Verwaltung
- URL-Duplikat-Prevention

**Modul-Kategorie:** Integration/Extension Module  
**Komplexität:** ⭐⭐ (Niedrig - einfache URL-Mappings)

## Architektur & Design Patterns

### Kern-Klassen
- **ProductUrlRewriteRepository**: Persistiert URL Rewrites
- **UrlRewriteProcessor**: Service Layer für URL-Verarbeitung
- **UrlRewriteValidator**: Validiert URLs und Request Paths
- **RequestPathResolver**: Resolved Request Paths zu Target Paths
- **RedirectTypeHandler**: Verwaltet Redirect Types (301/302)
- **UrlDuplicateChecker**: Prüft auf URL-Duplikate

### Verwendete Patterns
- **Observer Pattern**: Zur Einklinken in Import-Lifecycle
- **Repository Pattern**: Für abstrakte Datenschicht
- **Service Layer Pattern**: Geschäftslogik isoliert
- **Strategy Pattern**: Verschiedene Redirect Handling Strategien

## Abhängigkeiten

### TechDivision Dependencies
- **import-product** ^26.2 - Base Product Importer (Parent)
- **import-converter** - Data Conversion Framework

## Wichtige Entry Points

```php
// URL Rewrite Repository
ProductUrlRewriteRepository::create($row): void
ProductUrlRewriteRepository::findByProductId($productId): array
ProductUrlRewriteRepository::findByRequestPath($requestPath): UrlRewrite
```

## Database Schema

**url_rewrite** - URL Rewrite Einträge
- `entity_type` - 'product'
- `entity_id` - Product ID
- `request_path` - SEO URL (z.B. 'my-awesome-product.html')
- `target_path` - Ziel (z.B. 'catalog/product/view/id/123')
- `store_id` - Store Context
- `redirect_type` - 0=permanent (301), 1=temporary (302)

## Common Use Cases

### Use Case 1: Custom SEO URLs
```php
// CSV:
// sku,request_path
// PROD-001,my-awesome-product.html
// Erstellt URL Rewrite: my-awesome-product.html → catalog/product/view/id/123
```

### Use Case 2: Redirects bei URL-Änderungen
```php
// CSV: alte → neue URL mit 301 Redirect
// old_path,new_path,redirect_type
// old-product.html,new-product.html,301
```

## Performance Considerations

1. **Request Path Lookups**: Paths werden schnell nachgeschlagen
2. **Store Context**: Separate URLs pro Store
3. **Duplikat-Prüfung**: Request Paths müssen unique sein pro Store
4. **Index Performance**: request_path MUSS indexiert sein

## Hints für KI-Agenten

### Kritisches Verständnis
1. **SEO-fokussiert**: Menschenlesbare URLs für Google
2. **Store-specific**: Pro Store unterschiedliche URLs
3. **Redirect Types**: 301=permanent, 302=temporary
4. **Duplikat-Prevention**: Request Path muss unique pro Store sein

### Häufige Fehler
- ❌ Request Paths nicht unique (Duplicates)
- ❌ Store Context vergessen
- ❌ Redirect Types verwechselt
- ❌ Keine Indizes auf request_path

## Known Limitations

- **Uniqueness**: Request Path muss unique pro Store
- **Store-Kontext**: URLs nicht automatisch für alle Stores
- **No Validation**: URLs werden nicht auf Gültigkeit geprüft

## Related Modules

- **import-product** - Base Product Importer
- **import-product-ee** - EE Product Extensions

## Zusammenfassung

`import-product-url-rewrite` ist ein **Tier 5 Modul** für SEO URL-Management. Verwaltet Request Paths, Target Paths und Redirect Types mit Store-Kontext.

**Für Agenten:** URL Rewrite Importer mit SEO und Redirect-Management.