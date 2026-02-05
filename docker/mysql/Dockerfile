# **************************************************************************** #
#     File: Dockerfile                                                         #
#     Author: atucci <atucci@student.42.fr>                                    #
#     Created: 2026/02/02 16:45:32                                             #
#     Updated: 2026/02/05 21:09:14                                             #
#     System: Linux [e4r3p4.42roma.it]                                         #
#     Hardware: Intel Core i5-8600 | RAM: 15GB                                 #
# **************************************************************************** #

FROM mysql:5.7

# Set environment variables for better initialization control
ENV MYSQL_INITDB_SKIP_TZINFO=1

# Create the init directory
RUN mkdir -p /docker-entrypoint-initdb.d/

# Copy SQL files with explicit ordering
COPY ./db_schema/SQL/01_food_delivery.sql /docker-entrypoint-initdb.d/01_food_delivery.sql
COPY ./db_schema/SQL/02_seed.sql /docker-entrypoint-initdb.d/02_seed.sql

# Copy CSV if needed for seeding

# Set proper permissions (critical for 42 network drives)
RUN chmod -R 755 /docker-entrypoint-initdb.d/ && \
    chmod 644 /docker-entrypoint-initdb.d/*.sql

# Ensure files are readable by mysql user
RUN chown -R mysql:mysql /docker-entrypoint-initdb.d/

# Verify files are in place (debugging)
RUN echo "=== Files in init directory ===" && \
    ls -lah /docker-entrypoint-initdb.d/ && \
    echo "=== Content check ===" && \
    head -n 5 /docker-entrypoint-initdb.d/01_food_delivery.sql && \
    head -n 5 /docker-entrypoint-initdb.d/02_seed.sql

# COPY THE FIX SCRIPT
COPY ./docker/mysql/ensure-seeded.sh /usr/local/bin/ensure-seeded.sh

# Make it executable and fix line endings (crucial for Windows/Linux sync)
RUN chmod +x /usr/local/bin/ensure-seeded.sh && \
    sed -i 's/\r$//' /usr/local/bin/ensure-seeded.sh

# Health check to verify database is ready
HEALTHCHECK --interval=10s --timeout=5s --start-period=30s --retries=5 \
    CMD mysqladmin ping -h localhost -u root -p${MYSQL_ROOT_PASSWORD} || exit 1
