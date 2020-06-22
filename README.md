# Sonora API

## FINAL PROJECT IW H2

Top 1 Billboard 100 from 2000 to the end of 2019.

Stack:

- SQLite
- Lumen
- [Dataset](https://data.world/kcmillersean/billboard-hot-100-1958-2017)

Requirements:

- SQLite
- PHP >=7.3

Setup:

- Copy `.env.sample` to `.env` and configure accordingly
- Create a database from the dataset (*Hot 100 Audio features.csv* to a *songs_data* table and *Hot Stuff* to a *songs* table, convert columns name to snake_case)
- Put sqlite database file in `./database/database.sqlite`
- Serve ./public folder to visitors

### Endpoint Documentation

`/`: test if the api is up & running

`/songs`: list song_id, performer, name *as song* from all songs

`/songs/{song_id}`: get all song data

`/songs/search/{query}`: search a specific song by name or by performer

`/genres`: list all genres

`/genres/{genre}`: list song_id, performer, name *as song* from all songs from this genre

`/genres/search/{query}`: search a specific genre by name

`/bpm/{value}/songs`: list song_id, performer, name *as song* from songs with specific bpm (+/- 5)

`/average`: computed values