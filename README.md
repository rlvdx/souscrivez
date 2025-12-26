# Souscrivez

## Purpose
Trace what paid subscriptions to services like music streaming, password manager or email are currently active, who participates to them and when payments are due.

### Features
1. The admin can create subscriptions (a subscription has a service name, a number of available seats,a subscribed on date, a frequency and a price)
2. The admin can add participants (first name, last name, email) to subscriptions
3. The admin can see who subscribed to a service, what payments were made and when next payments are due
4. Payments of a given participant are summed up (all services combined in the least number of payments)

## Architecture
- it respects the DDD (domain-driven design) principles and the hexagonal architecture
- it uses the CQRS (command query responsibility segregation) pattern
- all features are covered by feature tests (written from the point of view of the user)

## Folder structure
- app/Domain contains the domain code (core business entities and logic that exist on their own)
- app/Application contains the application layer
- app/Infrastructure contains the infrastructure layer (adapters of the ports and adapters to external services)

## Technologies
- PHP 8.5
- PHPUnit
- SQLite
- Symfony Messenger
