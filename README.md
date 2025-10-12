# Laravel ZIP API - Kollár András 13.P

| Method | Endpoint | Auth? | Description |
| ------ | -------- | ----- | ----------- |
| POST | `/user/login` | N | Login and get a token |
| GET | `/zip-code` | N | Get the list of zip codes |
| GET | `/zip-code/{id}` | N | Get a single zip code |
| POST | `/zip-code/{id}` | Y | Update a zip code |
| POST | `/zip-code/create` | Y | Create a new zip code |
| POST | `/zip-code/{id}/delete` | Y | Delete a zip code |
