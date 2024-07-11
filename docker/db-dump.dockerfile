FROM tianon/true:multiarch

COPY database.sql /docker-entrypoint-initdb.d/100-database.sql

VOLUME [ "/docker-entrypoint-initdb.d" ]
