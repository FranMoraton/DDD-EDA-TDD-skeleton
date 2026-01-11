# AGENTS.md — Agents Module (DDD-EDA-TDD-skeleton)

This file guides coding agents working on the Agents module. It follows AGENTS.md best practices to make setup, architecture, migrations, testing, and conventions obvious.

---

## Quickstart

### Setup commands

- Install deps: `make composer-install`
- Start services: `make start`
- Initialize env + migrations: `make migrations`
- Run unit tests: `make tests`
- Run BDD tests: `make behat`
- Code quality (CI parity): `make grumphp`
- Fix code style: `make fix-cs`

### Dev environment tips

- PHP shell: `make bash`
- Tail logs: `make logs`
- Stop stack: `make stop`
- Full init flow: `make init`

### Code style & quality

- PHPCS (PSR-12), PHPStan, GrumPHP enforced on CI
- Prefer value objects; avoid primitives in domain APIs
- Keep functions small and explicit; avoid deep nesting

---

## Architecture & Patterns

- **Layered architecture**: Application, Domain, Infrastructure
- **DDD**: Aggregates, value objects, repositories, domain events
- **EDA**: Commands → Handlers → Domain events → Published via Messenger
- **TDD**: PHPUnit for units; Behat for acceptance; write tests first

### Technology stack

- Symfony, Doctrine DBAL (PostgreSQL), AMQP (Symfony Messenger), Valkey cache
- Tests: PHPUnit + Behat; Quality: GrumPHP (PHPCS, PHPStan)

---

## Module structure

### Directory layout

```
src/
└── Agent/
    ├── Application/
    │   ├── Command/Agent/{Create,Update,Delete,Start}/
    │   └── Query/Agent/{ById,Search}/
    ├── Domain/
    │   ├── Model/Agent/{Agent.php, AgentRepository.php, Criteria, Event, Exception, ValueObject}
    │   └── Service/Agent/AgentValidator.php
    └── Infrastructure/
        ├── Adapter/{Amqp,Command,RestApi}/
        └── Domain/Model/Agent/{DbalArrayAgentMapper.php, DbalAgentRepository.php}
```

### Test layout

```
tests/
└── Agent/
    ├── Application/Command/Agent/Create/{RandomCreateCommand.php, CreateCommandHandlerTest.php}
    ├── Domain/Model/Agent/RandomAgentGenerator.php
    ├── Infrastructure/Domain/Model/Agent/{DbalAgentContext.php, DbalAgentRepositoryTest.php}
    └── Entrypoint/features/{api,command,event}/Agent/{create,update,delete,get_by_id,search}.feature
```

---

## Event flow

1. API receives request (REST controller)
2. Controller builds Command/Query and dispatches on bus
3. Command handler loads/creates aggregate and applies domain logic
4. Aggregate records domain events
5. Publisher middleware emits events to event bus and persists them
6. Transport publishes asynchronously via AMQP

### Message naming

- Commands: `company.{module}.{version}.command.{entity}.{action}`
- Events: `company.{module}.{version}.domain_event.{entity}.{action}`
- Queries: `company.{module}.{version}.query.{entity}.{action}`

---

## Database migrations (required)

All entities MUST include optimistic locking + timestamps to work with `DbalRepository` and support Criteria queries.

### Required columns (only for migrations)

1. `id` (UUID/VARCHAR PRIMARY KEY)
2. `created_at` (TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP)
3. `updated_at` (TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP)
4. `version` (INT NOT NULL DEFAULT 1)

### Required indexes

- Index all fields used in Criteria filters (e.g., `status`, `name`)
- Index time-based ordering fields (`created_at`, `updated_at`)
- Index foreign keys; use composite indexes where relevant
- **GIN index** for JSONB array columns used in Criteria (required for `@>` containment operator)

### JSONB Index Strategy

When filtering JSONB array columns, create appropriate indexes:

```sql
-- GIN index for containment queries (@> operator) - used for EQUALS on array elements
CREATE INDEX idx_table_column_gin ON table USING GIN (column);

-- Expression index for specific nested field comparisons (>, <, >=, <=)
CREATE INDEX idx_table_column_field ON table ((column->>'field'));
```

### Example

```php
final class Version20250101000000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE agents (
                id UUID PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                status VARCHAR(50) NOT NULL,
                configuration JSONB NOT NULL,
                slots JSONB NOT NULL DEFAULT \'[]\',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                version INT NOT NULL DEFAULT 1
            )
        ');

        // Simple column indexes
        $this->addSql('CREATE INDEX idx_agents_status ON agents (status)');
        $this->addSql('CREATE INDEX idx_agents_name ON agents (name)');
        $this->addSql('CREATE INDEX idx_agents_created_at ON agents (created_at)');
        
        // GIN index for JSONB array containment queries (slots[].field = 'value')
        $this->addSql('CREATE INDEX idx_agents_slots_gin ON agents USING GIN (slots)');
    }
}
```

### Checklist

- [ ] Columns `id`, `created_at`, `updated_at`, `version`
- [ ] Indexes for all Criteria fields
- [ ] Optimistic locking respected in updates
- [ ] **GIN index** for JSONB array columns used in Criteria filters

---

## Testing instructions (TDD required)

### Unit tests (PHPUnit)

- Run: `make tests`
- Write object mothers: `tests/Agent/Application/.../RandomCreateCommand.php`, `tests/Agent/Domain/.../RandomAgentGenerator.php`
- Handlers must be fully covered (happy path + error path)

### Acceptance (Behat)

- Run all: `make behat`
- Run specific: `make behat -- tests/Agent/Entrypoint/features/api/Agent/create.feature`
- Use `DbalAgentContext` to seed database state
- Add context to behat.yml.dist
- Event bus wiring: when `AgentWasCreated` is recorded, it must be published on `messenger_event.bus` and subscribed handlers should trigger follow-up commands if defined.
- Prefer using test transports/spy middleware to assert message names and payloads without external brokers.


### Must-have scenarios

- Command: create agent → `AgentWasCreated` dispatched
- API: POST /agents creates agent (201)
- API: GET /agents/{id} returns serialized aggregate

---

## Configuration

### Base DI exclusions

Exclude Agent handlers/adapters from the app-wide autowiring, as they are declared in the Agent context file. Test object mothers live under `tests/` and must not be autowired.

```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true

  App\:
    resource: '../src/'
    exclude:
      - '../src/Agent/Application/Command/*/*/*Handler.php'
      - '../src/Agent/Application/Query/*/*/*Handler.php'
      - '../src/Agent/Infrastructure/Adapter/*'
```

### Services (context-scoped)

Add `config/context/agent/services.yaml` and import it from `config/services.yaml`.

```yaml
imports:
  - { resource: context/agent/services.yaml }
```

```yaml
services:
  _defaults:
    autowire: true
    autoconfigure: true

  App\Agent\Infrastructure\Adapter\RestApi\:
    resource: '../../../src/Agent/Infrastructure/Adapter/RestApi/*'
    bind:
      Symfony\Component\Messenger\MessageBusInterface $commandBus: '@messenger_command.bus'
      Symfony\Component\Messenger\MessageBusInterface $queryBus: '@messenger_query.bus'
    tags:
      - { name: controller.service_arguments }

  App\Agent\Application\Command\:
    autowire: true
    autoconfigure: false
    resource: '../../../src/Agent/Application/Command/*/*/*Handler.php'
    tags:
      - { name: messenger.message_handler, bus: messenger_command.bus }

  App\Agent\Application\Query\:
    autowire: true
    autoconfigure: false
    resource: '../../../src/Agent/Application/Query/*/*/*Handler.php'
    tags:
      - { name: messenger.message_handler, bus: messenger_query.bus }

  App\Agent\Domain\Model\Agent\AgentRepository:
    class: App\Agent\Infrastructure\Domain\Model\Agent\DbalAgentRepository
```

### Messenger routing

```yaml
framework:
  messenger:
    transports:
      async: '%env(MESSENGER_TRANSPORT_DSN)%'
    routing:
      'App\Agent\Application\Command\*': async
      'App\Agent\Domain\Model\Agent\Event\*': async
```

---

## Criteria Convention

The `Criteria` pattern provides a unified API for filtering, pagination, and ordering. Field notation determines how the infrastructure translates filters to SQL.

### Field Notation

| Notation | Type | Description | Example |
|----------|------|-------------|---------|
| `field` | Simple column | Direct table column | `status`, `url` |
| `column.nested` | JSONB object | Nested field in JSONB object | `config.model`, `config.timeout` |
| `column[].nested` | JSONB array | Field inside JSONB array elements | `slots[].activation_date`, `slots[].status` |

### Defining Allowed Fields

⚠️ **Important**: JSONB field names MUST match exactly how they are stored in the database (usually camelCase from `jsonSerialize()`).

```php
final class DiscountsCriteria extends Criteria
{
    protected function allowedFields(): array
    {
        return [
            // Simple columns (snake_case - DB convention)
            'url' => 'string',
            'status' => 'string',
            
            // JSONB object fields (column.nestedField - match jsonSerialize)
            'config.model' => 'string',
            'config.timeout' => 'int',
            
            // JSONB array fields (column[].nestedField - match jsonSerialize)
            'slots[].activationDate' => 'datetime',    // camelCase!
            'slots[].deactivationDate' => 'datetime',  // camelCase!
            'slots[].status' => 'string',
            'slots[].code' => 'string',
        ];
    }
}
```

### Usage

```php
// All field types use the same withFilter() method
$criteria = new DiscountsCriteria();

// Simple column filter
$criteria->withFilter('status', 'active', Operator::EQUALS);

// JSONB object filter
$criteria->withFilter('config.model', 'gpt-4', Operator::EQUALS);

// JSONB array filter (field names match jsonSerialize output)
$criteria->withFilter('slots[].activationDate', $date, Operator::GREATER_THAN);
$criteria->withFilter('slots[].code', 'SUMMER2025', Operator::EQUALS);

// Pagination and ordering
$criteria->withLimit(50);
$criteria->withOffset(0);
$criteria->withOrder('created_at', Direction::DESC);
```

### SQL Translation

| Notation | Operator | Generated SQL |
|----------|----------|---------------|
| `status` | `=` | `t.status = 'active'` |
| `config.model` | `=` | `(t.config->>'model') = 'gpt-4'` |
| `slots[].status` | `=` | `t.slots @> '[{"status":"active"}]'::jsonb` |
| `slots[].activationDate` | `>` | `EXISTS (SELECT 1 FROM jsonb_array_elements(t.slots) elem WHERE (elem->>'activationDate')::text > '...')` |

### Required Indexes

| Field Type | Required Index |
|------------|----------------|
| Simple column | `CREATE INDEX idx_table_field ON table (field)` |
| JSONB object | `CREATE INDEX idx_table_col_field ON table ((col->>'field'))` |
| JSONB array (=) | `CREATE INDEX idx_table_col_gin ON table USING GIN (col)` |
| JSONB array (>, <) | GIN index + query uses EXISTS subquery |

---

## Search Query System

The Search Query system provides a unified way to filter aggregates via REST API query parameters. The controller passes raw query params to the SearchQuery, which then delegates to BySearchCriteria for parsing and validation.

### Query Parameter Format

Filters use the format `field=OPERATOR::value` or `field=value` (defaults to EQUALS):

| Format | Operator | Example |
|--------|----------|---------|
| `field=value` | EQUALS | `?status=ACTIVE` |
| `field=EQUALS::value` | EQUALS | `?status=EQUALS::ACTIVE` |
| `field=IN::val1,val2` | IN | `?status=IN::ACTIVE,SCHEDULED` |
| `field=GREATER_THAN::value` | > | `?createdAt=GREATER_THAN::2025-01-01` |
| `field=LESS_THAN::value` | < | `?price=LESS_THAN::100` |
| `field=GREATER_THAN_OR_EQUALS::value` | >= | `?age=GREATER_THAN_OR_EQUALS::18` |
| `field=LESS_THAN_OR_EQUALS::value` | <= | `?score=LESS_THAN_OR_EQUALS::50` |
| `field=LIKE::value` | LIKE | `?name=LIKE::%test%` |
| `field=NOT_EQUALS::value` | != | `?status=NOT_EQUALS::DELETED` |
| `field=IS_NULL` | IS NULL | `?url=IS_NULL` |
| `field=IS_NOT_NULL` | IS NOT NULL | `?url=IS_NOT_NULL` |

**Note**: `IS_NULL` and `IS_NOT_NULL` are valueless operators - they don't use the `::value` format.

### Architecture Flow

```
Controller
    │
    └── passes $request->query->all()
            │
            ▼
SearchQuery::create(array $queryParams)
    │
    └── rebuildPayload() extracts:
        - items_per_page, page → pagination
        - remaining params → filters
            │
            ▼
SearchQueryHandler
    │
    └── BySearchCriteria::execute($itemsPerPage, $page, $filters)
            │
            ▼
BySearchCriteria::applyFilters()
    │
    ├── Skips unknown fields (isFieldAllowed)
    ├── Parses OPERATOR::value format
    ├── Casts value to type (getFieldType)
    └── Calls withFilter() → type validation
```

### Implementation Pattern

**1. Controller** - Pass raw query params:

```php
public function search(Request $request): JsonResponse
{
    $result = $this->publishQuery(
        SearchQuery::create($request->query->all())
    );

    return new JsonResponse($result, Response::HTTP_OK);
}
```

**2. SearchQuery** - Extract pagination and filters:

```php
public static function create(array $queryParams): self
{
    return self::fromPayload(Uuid::v4(), $queryParams);
}

public function rebuildPayload(): void
{
    $payload = $this->payload();
    
    $this->itemsPerPage = isset($payload['items_per_page']) ? (int) $payload['items_per_page'] : null;
    $this->page = isset($payload['page']) ? (int) $payload['page'] : null;
    
    unset($payload['items_per_page'], $payload['page']);
    $this->filters = $payload;
}
```

**3. BySearchCriteria** - Uses `FilterParser` from System:

```php
use App\System\Domain\Criteria\FilterParser;

class BySearchCriteria
{
    private const int DEFAULT_PAGE = 1;
    private const int DEFAULT_ITEMS_PER_PAGE = 50;

    public static function execute(?int $itemsPerPage, ?int $page, array $filters): EntityCriteria
    {
        $criteria = new EntityCriteria();

        // FilterParser handles: parsing OPERATOR::value, type casting, validation
        FilterParser::applyFilters($criteria, $filters);

        $effectivePage = $page ?? self::DEFAULT_PAGE;
        $effectiveItemsPerPage = $itemsPerPage ?? self::DEFAULT_ITEMS_PER_PAGE;

        $criteria->withOffset($criteria->calculatePaginationOffSet($effectivePage, $effectiveItemsPerPage));
        $criteria->withLimit($effectiveItemsPerPage);

        return $criteria;
    }
}
```

**4. FilterParser** (System) - Centralized filter parsing:

```php
// src/System/Domain/Criteria/FilterParser.php
final class FilterParser
{
    public static function applyFilters(Criteria $criteria, array $filters): void
    {
        foreach ($filters as $field => $rawValue) {
            if (!$criteria->isFieldAllowed($field)) {
                continue;  // Skip unknown fields silently
            }

            [$operator, $value] = self::parseFilterValue((string) $rawValue);
            $typedValue = self::castValue($value, $criteria->getFieldType($field));
            
            $criteria->withFilter($field, $typedValue, $operator);
        }
    }
    
    // Parses "OPERATOR::value" or returns EQUALS for plain values
    // Handles IN with comma-separated values
    // Casts to int, float, bool, datetime based on allowedFields() type
}
```

### API Examples

```bash
# Simple filter
GET /api/1.0/discounts?url=https://example.com

# Filter with explicit EQUALS
GET /api/1.0/discounts?status=EQUALS::ACTIVE

# Multiple values with IN
GET /api/1.0/discounts?status=IN::ACTIVE,SCHEDULED,PENDING

# Comparison operators
GET /api/1.0/discounts?slots[].activationDate=GREATER_THAN::2025-01-01

# Combined filters with pagination
GET /api/1.0/discounts?url=https://example.com&status=ACTIVE&page=1&items_per_page=25

# Multiple filters
GET /api/1.0/domains?url=https://example.com&status=IN::ACTIVE,PENDING

# Filter for NULL values
GET /api/1.0/discounts?url=IS_NULL

# Filter for NOT NULL values
GET /api/1.0/discounts?url=IS_NOT_NULL

# Combined with other filters
GET /api/1.0/discounts?status=ACTIVE&config.model=IS_NOT_NULL
```

### Type Casting

Values are automatically cast based on the field type defined in `allowedFields()`:

| Type | Cast | Example |
|------|------|---------|
| `string` | No cast | `"active"` |
| `int` | `(int)` | `"123"` → `123` |
| `float` | `(float)` | `"3.14"` → `3.14` |
| `bool` | `filter_var(..., FILTER_VALIDATE_BOOLEAN)` | `"true"` → `true` |
| `datetime` | `new \DateTimeImmutable($value)` | `"2025-01-01"` → `DateTimeImmutable` |

### Error Handling

- **Unknown fields**: Silently ignored (allows API evolution)
- **Invalid operator**: Treated as EQUALS with full value
- **Type mismatch**: Exception thrown by `Criteria::withFilter()`
- **Empty IN values**: Exception thrown by `Criteria::assertInFilterValue()`
- **Null/empty values**: Silently ignored (use `IS_NULL` to filter for NULL values)

### Null Value Behavior

| Query Param | Behavior |
|-------------|----------|
| `?url=` | Ignored (empty string) |
| `?url` (no value) | Ignored (null) |
| `?url=IS_NULL` | Filters where `url IS NULL` |
| `?url=IS_NOT_NULL` | Filters where `url IS NOT NULL` |
| `?url=value` | Filters where `url = 'value'` |

---

## Search Response Structure (Required)

All Search endpoints **MUST** return a `SearchResponse` DTO with pagination metadata. This ensures consistent API responses across all search operations.

### Response Structure

```json
{
    "items": [...],
    "total": 150,
    "page": 1,
    "items_per_page": 50,
    "total_pages": 3
}
```

| Field | Type | Description |
|-------|------|-------------|
| `items` | array | The items for the current page |
| `total` | int | Total count of items matching the filters (without pagination) |
| `page` | int | Current page number |
| `items_per_page` | int | Number of items per page |
| `total_pages` | int | Total number of pages (calculated) |

### SearchResponse DTO

Use `App\System\Application\Query\SearchResponse`:

```php
use App\System\Application\Query\SearchResponse;

final readonly class SearchQueryHandler
{
    private const int DEFAULT_PAGE = 1;
    private const int DEFAULT_ITEMS_PER_PAGE = 50;

    public function __construct(private EntityRepository $repository)
    {
    }

    public function __invoke(SearchQuery $query): SearchResponse
    {
        $page = $query->page() ?? self::DEFAULT_PAGE;
        $itemsPerPage = $query->itemsPerPage() ?? self::DEFAULT_ITEMS_PER_PAGE;

        // Criteria WITH pagination for fetching items
        $paginatedCriteria = BySearchCriteria::execute(
            $itemsPerPage,
            $page,
            $query->filters(),
        );

        // Criteria WITHOUT pagination for total count
        $countCriteria = BySearchCriteria::execute(
            null,
            null,
            $query->filters(),
        );

        $items = $this->repository->search($paginatedCriteria);
        $total = $this->repository->count($countCriteria);

        return SearchResponse::create($items, $total, $page, $itemsPerPage);
    }
}
```

### Key Points

1. **Two Criteria needed**: One with pagination (for items), one without (for total count)
2. **Pass `null` for count**: `BySearchCriteria::execute(null, null, $filters)` skips limit/offset
3. **Return type**: Handler returns `SearchResponse`, not `array`
4. **JsonSerializable**: `SearchResponse` implements `JsonSerializable` for automatic JSON encoding

### Example API Response

```bash
GET /api/1.0/discounts?status=ACTIVE&page=1&items_per_page=2
```

```json
{
    "items": [
        {
            "id": "aaaaaaaa-1111-1111-1111-111111111111",
            "url": "https://example1.com",
            "status": "ACTIVE",
            "slots": []
        },
        {
            "id": "bbbbbbbb-2222-2222-2222-222222222222",
            "url": "https://example2.com",
            "status": "ACTIVE",
            "slots": []
        }
    ],
    "total": 25,
    "page": 1,
    "items_per_page": 2,
    "total_pages": 13
}
```

---

## Best practices

- Prefer value objects (e.g., `Id`, `Name`, `Status`, `Configuration`)
- Record events inside aggregates; let middleware publish
- Use `Criteria` for all repository reads (index the searched fields)
- Keep controllers thin: build messages, dispatch buses
- Tests first; acceptance features mirror user workflows

### External integrations (Anti-Corruption Layer required)

- Always add an Anti-Corruption Layer (ACL) for external resources (HTTP APIs, queues, webhooks).
- Isolate external schemas behind `Infrastructure/Adapter/*` and mappers; never leak external DTOs into Domain/Application.
- Validate and normalize inputs, then convert to domain value objects/events before handing off to handlers.
- Apply resilience: timeouts, retries with backoff, circuit breaker, and idempotency where applicable.

---

## Reference scenarios

```gherkin
Feature: Create Agent via API
  Scenario: Create a new agent
    Given the API is ready
    When I send a POST request to "/agents" with body:
    """
    {"id":"agent-001","name":"Support Agent","configuration":{"model":"gpt-4","temperature":0.7}}
    """
    Then the response status code should be 201
    And the response should contain "message"
```

```gherkin
Feature: Create Agent command
  Scenario: Dispatch CreateCommand on command bus
    Given the buses are clean
    When the following command:
    """
    {
      "message_id": "69d9b5a9-bfd1-43d6-9f72-64e450a8c760",
      "type": "company.application.1.command.agent.create",
      "payload": {
        "id": "agent-001",
        "name": "Support Agent",
        "configuration": { "model": "gpt-4", "temperature": 0.7 }
      }
    }
    """
    Then the "company.application.1.domain_event.agent.was_created" event should be dispatched
```

```gherkin
Feature: Agent was created event
  Scenario: Publish to event bus and validate payload
    Given the buses are clean
    When the following command:
    """
    {
      "message_id": "69d9b5a9-bfd1-43d6-9f72-64e450a8c760",
      "type": "company.application.1.command.agent.create",
      "payload": {
        "id": "agent-001",
        "name": "Support Agent",
        "configuration": { "model": "gpt-4", "temperature": 0.7 }
      }
    }
    """
    Then the "company.application.1.domain_event.agent.was_created" event should be dispatched
```

---

## Consumers (optional during development)

- Commands: `make consume-commands`
- Events: `make consume-events`

---

## Important Guidelines

### ⚠️ Always Create Tests

**Tests are NOT optional.** Every implementation must include comprehensive test coverage:

- ✅ **Unit Tests (PHPUnit)**: Test each handler, domain model, and service
- ✅ **Acceptance Tests (Behat)**: Test complete user workflows and API endpoints
- ✅ **Test Coverage**: Aim for high coverage (>80%) for critical paths

Tests should be created FIRST (TDD approach) before implementation:

```
1. Write test cases
2. Write implementation
3. Verify tests pass
4. Refactor if needed
```

Failure to include tests will result in incomplete implementations.

### ⚠️ Do NOT Create README Files Unless Explicitly Requested

**Documentation files are not automatically created.** Guidelines:

- ❌ **Do NOT create**: README.md, module documentation, or other markdown files unless specifically requested
- ✅ **DO create**: Only if the user explicitly asks for documentation
- ❌ **DO NOT include**: Code comments, docstrings, and inline documentation within the code itself
- ✅ **DO provide**: Usage examples in tests and acceptance test features

If a user wants documentation, they will explicitly ask:
```
"Create a README for this module"
"Add documentation explaining how to use X"
"Create a guide for the API"
```

This keeps the repository clean and focused on code quality over documentation volume.

## Notes

- The Agents module adheres to DDD, EDA, and TDD rigorously
- Keep AGENTS.md aligned with evolving conventions and CI checks
