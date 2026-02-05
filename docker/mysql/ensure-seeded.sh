#!/bin/bash
# **************************************************************************** #
#     ensure-seeded.sh                                                         #
#     Runs AFTER MySQL starts to verify and force seeding if needed           #
# **************************************************************************** #

set -e

MYSQL_ROOT_PASSWORD="${MYSQL_ROOT_PASSWORD:-rootpass}"
MYSQL_DATABASE="${MYSQL_DATABASE:-food_delivery}"
MAX_RETRIES=30
RETRY_INTERVAL=2

echo "⏳ Waiting for MySQL to be ready..."

# Wait for MySQL to be responsive
for i in $(seq 1 $MAX_RETRIES); do
    if mysqladmin ping -h localhost -uroot -p"${MYSQL_ROOT_PASSWORD}" --silent 2>/dev/null; then
        echo "✅ MySQL is ready!"
        break
    fi
    echo "   Attempt $i/$MAX_RETRIES: MySQL not ready yet..."
    sleep $RETRY_INTERVAL
done

# Check if database exists
echo "🔍 Checking if database '$MYSQL_DATABASE' exists..."
DB_EXISTS=$(mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -e "SHOW DATABASES LIKE '${MYSQL_DATABASE}';" --batch --skip-column-names 2>/dev/null | wc -l)

if [ "$DB_EXISTS" -eq 0 ]; then
    echo "❌ Database does not exist! Creating..."
    mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -e "CREATE DATABASE IF NOT EXISTS ${MYSQL_DATABASE};"
fi

# Check if tables exist and have data
echo "🔍 Checking if database is seeded..."
TABLE_COUNT=$(mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -D "${MYSQL_DATABASE}" -e "SHOW TABLES;" --batch --skip-column-names 2>/dev/null | wc -l)

if [ "$TABLE_COUNT" -eq 0 ]; then
    echo "⚠️  No tables found! Running initialization scripts manually..."
    
    # Run schema
    if [ -f /docker-entrypoint-initdb.d/01_food_delivery.sql ]; then
        echo "   Running 01_food_delivery.sql..."
        mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -D "${MYSQL_DATABASE}" < /docker-entrypoint-initdb.d/01_food_delivery.sql
        echo "   ✅ Schema created"
    fi
    
    # Run seed
    if [ -f /docker-entrypoint-initdb.d/02_seed.sql ]; then
        echo "   Running 02_seed.sql..."
        mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -D "${MYSQL_DATABASE}" < /docker-entrypoint-initdb.d/02_seed.sql
        echo "   ✅ Seed data inserted"
    fi
else
    echo "✅ Found $TABLE_COUNT tables"
    
    # Check if specific tables have data
    COUNTRY_COUNT=$(mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -D "${MYSQL_DATABASE}" -e "SELECT COUNT(*) FROM country;" --batch --skip-column-names 2>/dev/null || echo "0")
    
    if [ "$COUNTRY_COUNT" -eq 0 ]; then
        echo "⚠️  Tables exist but no seed data found! Running seed script..."
        if [ -f /docker-entrypoint-initdb.d/02_seed.sql ]; then
            mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -D "${MYSQL_DATABASE}" < /docker-entrypoint-initdb.d/02_seed.sql
            echo "   ✅ Seed data inserted"
        fi
    else
        echo "✅ Database is properly seeded with $COUNTRY_COUNT countries"
    fi
fi

echo "🎉 Database verification complete!"
