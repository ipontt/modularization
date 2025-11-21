# Laravel application as a Modular Monolith

E-commerce example.

Services:
- postgresql
- valkey

Ideally, each module should be independent and loosely coupled. 

Here's how things started:

![starting_point](starting_point.png)

Here's how things ended up after an initial refactor

![after_refactor](after_refactor.png)

And then after even more decoupling by using events

![after_events](after_events.png)
