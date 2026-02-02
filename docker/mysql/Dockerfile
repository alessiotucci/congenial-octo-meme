# **************************************************************************** #
#     File: Dockerfile                                                         #
#     Author: atucci <atucci@student.42.fr>                                    #
#     Created: 2026/02/02 16:45:32                                             #
#     Updated: 2026/02/02 16:47:17                                             #
#     System: Linux [e4r3p4.42roma.it]                                         #
#     Hardware: Intel Core i5-8600 | RAM: 15GB                                 #
# **************************************************************************** #

#TODO: issue with the 42 pc 
################################################################################
# This often happens on University Computers or WSL2 when the project is saved #
# on a network drive or the file system connection gets desynchronized.        #
################################################################################

# docker/mysql/Dockerfile
FROM mysql:5.7

# Instead of mapping the folder, we COPY the files inside.
# This makes the image self-contained and immune to "Stale File Handles".
COPY ./db_schema/SQL/ /docker-entrypoint-initdb.d/
