[![build status](https://git.et.tc/job-lion/job-lion-backend/badges/master/build.svg)](https://git.et.tc/job-lion/job-lion-backend/commits/master)
[![coverage report] (https://git.et.tc/job-lion/job-lion-backend/badges/master/coverage.svg)](https://git.et.tc/job-lion/job-lion-backend/commits/master)

# Job-Lion Backend

Backend (API) for Job-Lion

Documentation:
[API](https://api.staging.job-lion.et.tc/apidoc),
[Classes](https://api.staging.job-lion.et.tc/docs)

## Development
You will need to setup apache, php, mysql (e.g. [xampp](https://www.apachefriends.org/index.html)) and [composer](https://getcomposer.org/).
Then follow these steps:

- Clone this repository to your server folder

```
git clone https://git.et.tc/job-lion/job-lion-backend.git
cd job-lion-backend
```

- Install dependencies

```
composer install
```

- Copy conf/conf.sample.php to conf/conf.php and set options (mysql database details)

- Setup database

```
vendor/bin/doctrine-migrations migration:migrate
```

### Migrate database
You should run `vendor/bin/doctrine-migrations migration:migrate` after every pull, to bring the database up to date.

### Unit testing (Optional)
Install [phpunit](https://phpunit.de/manual/current/en/installation.html) and run it in the root folder.

### Generate API documentation (Optional)
Install [apidoc](https://www.npmjs.com/package/apidoc).

- Copy apidoc.json.dist to apidoc.json, replace `url` and `sampleUrl` with your local server url.
- Run this command in the root folder:

```
apidoc -o public/apidoc
```
The documentation can now be found in public/apidoc
