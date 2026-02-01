# **************************************************************************** #
#                                                                              #
#    Host: e4r2p4.42roma.it                                                    #
#    File: Makefile                                                            #
#    Created: 2026/01/17 17:56:27 | By: atucci <atucci@student.42              #
#    Updated: 2026/01/17 17:56:30                                              #
#    OS: Linux 6.5.0-44-generic x86_64 | CPU: Intel(R) Core(TM) i              #
#                                                                              #
# **************************************************************************** #

# Colors
GREEN  := \033[0;32m
YELLOW := \033[0;33m
RED    := \033[0;31m
BLUE   := \033[0;34m
NC     := \033[0m # No Color

# Default Target
all: check_deps up

# ------------------------------------------------------------------------------
# 1. Dependency Check (Gentle Warning Mode)
# ------------------------------------------------------------------------------
check_deps:
	@echo "$(BLUE)--- Checking System Dependencies ---$(NC)"
	@# Check OS
	@if [ "$$(uname)" = "Linux" ]; then \
		echo "$(GREEN)[OK] OS Detected: Linux$(NC)"; \
	elif [ "$$(uname)" = "Darwin" ]; then \
		echo "$(GREEN)[OK] OS Detected: macOS$(NC)"; \
	else \
		echo "$(YELLOW)[INFO] OS Detected: Windows/Other (Ensure you are using Git Bash or WSL)$(NC)"; \
	fi

	@# Check Docker
	@if command -v docker >/dev/null 2>&1; then \
		echo "$(GREEN)[OK] Docker is installed.$(NC)"; \
	else \
		echo "$(RED)[ERR] Docker is missing! The app will not run.$(NC)"; \
	fi

	@# Check Docker Compose
	@if command -v docker-compose >/dev/null 2>&1; then \
		echo "$(GREEN)[OK] Docker Compose is installed.$(NC)"; \
	else \
		echo "$(YELLOW)[WARN] Docker Compose not found (Are you using 'docker compose'?)$(NC)"; \
	fi

	@# Check PHP (Warning Only)
	@if command -v php >/dev/null 2>&1; then \
		echo "$(GREEN)[OK] Local PHP found (Version: $$(php -v | head -n 1 | cut -d ' ' -f 2)).$(NC)"; \
	else \
		echo "$(YELLOW)[WARN] Local PHP is missing. (This is fine, we use Docker).$(NC)"; \
	fi

	@# Check MySQL (Warning Only)
	@if command -v mysql >/dev/null 2>&1; then \
		echo "$(GREEN)[OK] Local MySQL client found.$(NC)"; \
	else \
		echo "$(YELLOW)[WARN] Local MySQL is missing. (This is fine, we use Docker).$(NC)"; \
	fi
	@echo "$(BLUE)--- Dependency Check Complete ---$(NC)\n"

# ------------------------------------------------------------------------------
# 2. Docker Management
# ------------------------------------------------------------------------------
up:
	@echo "$(GREEN)Building and Starting Containers...$(NC)"
	docker-compose up -d --build
	@echo "$(GREEN)App is running at: http://localhost:8080 (or your configured port)$(NC)"

down:
	@echo "$(RED)Stopping Containers...$(NC)"
	docker-compose down

restart: down up

logs:
	docker-compose logs -f app

# ------------------------------------------------------------------------------
# 3. Utilities
# ------------------------------------------------------------------------------
# Enter the PHP container shell
shell:
	docker-compose exec app bash

# Enter the Database container shell
db-shell:
	docker-compose exec db mysql -uuser -ppassword food_delivery

# Clean everything (Caution!)
clean:
	@echo "$(RED)Removing all containers, networks, and volumes...$(NC)"
	docker-compose down -v --remove-orphans
	@echo "$(YELLOW)Note: Database data has been wiped.$(NC)"
