DATE=$(shell date +%I:%M%p)
CHECK=\033[32m✔\033[39m
HR=\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#\#

build:
	@echo "\n${HR}"
	@echo "Building Flow Agora overrides..."
	@echo "${HR}\n"
	@compass compile
	@echo "Compiling Compass project...            ${CHECK} Done"
	@rm -rf .sass-cache
	@echo "Removing .sass-cache...                 ${CHECK} Done"
	@echo "\n${HR}"
	@echo "Flow Agora overrides successfully built at ${DATE}."
	@echo "${HR}\n"

all: build

watch:
	@echo "\n${HR}"
	@echo "Watching SCSS files for Flow Agora overrides..."
	@echo "${HR}\n"
	@compass watch
	@echo "Started watching modules/scss at ${DATE}..."
