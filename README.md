# Laravel ZIP API - Kollár András 13.P

| Method | Endpoint | Auth? | Description |
| ------ | -------- | ----- | ----------- |
| POST | `/users/login` | ❌ | Login and get a token |
| GET | `/zip-codes` | ❌ | Get the list of zip codes |
| GET | `/zip-codes/{id}` | ❌ | Get a single zip code |
| PUT | `/zip-codes/{id}` | ✅ | Update a zip code |
| POST | `/zip-codes` | ✅ | Create a new zip code |
| DELETE | `/zip-codes/{id}` | ✅ | Delete a zip code |
