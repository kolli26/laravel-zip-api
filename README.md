# Laravel ZIP API - Kollár András 13.P

Apidoc is available at: `/index.html`

| Method | Endpoint | Auth? | Description |
| ------ | -------- | ----- | ----------- |
| POST | `/users/login` | ❌ | Login and get a token |
| GET | `/zip-codes` | ❌ | Get the list of zip codes |
| GET | `/zip-codes/{id}` | ❌ | Get a single zip code |
| PUT | `/zip-codes/{id}` | ✅ | Update a zip code |
| POST | `/zip-codes` | ✅ | Create a new zip code |
| DELETE | `/zip-codes/{id}` | ✅ | Delete a zip code |
| GET | `/counties` | ❌ | Get the list of counties |
| GET | `/counties/{id}` | ❌ | Get a single county |
| GET | `/counties/{id}/abc` | ❌ | Get place name initials for a county |
| GET | `/counties/{id}/place-names` | ❌ | Get place names for a county |
| GET | `/counties/{county}/place-names/{placeName}` | ❌ | Get a single place name for a county |
| POST | `/counties` | ✅ | Create a new county |
| PUT | `/counties/{id}` | ✅ | Update a county |
| DELETE | `/counties/{id}` | ✅ | Delete a county |