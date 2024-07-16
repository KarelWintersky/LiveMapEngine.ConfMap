#!/usr/bin/make
PROJECT = confmap
PATH_PROJECT = $(DESTDIR)/var/www/$(PROJECT)
PATH_PUBLIC = $(PATH_PROJECT)/public
MANTICONF_DIR = manticonf
MANTICONF_PROJECT = livemap

help:
	@perl -e '$(HELP_ACTION)' $(MAKEFILE_LIST)

dchr:		##@development Publish release
	@dch --controlmaint --release --distribution unstable

dchv:		##@development Append release
	@export DEBEMAIL="karel.wintersky@gmail.com" && \
	export DEBFULLNAME="Karel Wintersky" && \
	echo "$(YELLOW)------------------ Previous version header: ------------------$(GREEN)" && \
	head -n 3 debian/changelog && \
	echo "$(YELLOW)--------------------------------------------------------------$(RESET)" && \
	read -p "Next version: " VERSION && \
	dch --controlmaint -v $$VERSION

update:		##@build Update project from GIT
	@echo Updating project from GIT
	git pull

build:		##@build Build project to DEB Package
	@echo Building project to DEB-package
	export COMPOSER_HOME=/tmp/ && dpkg-buildpackage -rfakeroot --no-sign #--compression=xz
	@rm ./configure-stamp ./build-stamp

make_deb: update build   ##@build Update project and build

rebuild_rt:	##@localhost Rebuild RT indexes only
	@php $(PATH_PROJECT)/admin.tools/tool.rebuild_rt_indexes.php

setup_env:	##@localhost Setup environment at localhost
	@echo Setting up local environment
	@mkdir -p $(PATH_PROJECT)/cache
	@mkdir -p $(PATH_PROJECT)/config
	@mkdir -p $(PATH_PROJECT)/logs

install: 	##@system Install package. Don't run it manually!!!
	@echo Installing...
	install -d $(PATH_PROJECT)
#	cp -r admin.cron $(PATH_PROJECT)
	cp -r admin.tools $(PATH_PROJECT)
	cp -r engine $(PATH_PROJECT)
	cp -r framework $(PATH_PROJECT)
	cp -r public $(PATH_PROJECT)
	cp -r templates $(PATH_PROJECT)
	cp -r composer.json $(PATH_PROJECT)
#	cp debian/makefile.production $(PATH_PROJECT)/makefile
	git rev-parse --short HEAD > $(PATH_PROJECT)/_version
	git log --oneline --format=%B -n 1 HEAD | head -n 1 >> $(PATH_PROJECT)/_version
	git log --oneline --format="%at" -n 1 HEAD | xargs -I{} date -d @{} +%Y-%m-%d >> $(PATH_PROJECT)/_version
	cd $(PATH_PROJECT)/ && composer install && rm composer.json
#	mkdir -p $(DESTDIR)/etc/$(MANTICONF_DIR)/conf.d/$(MANTICONF_PROJECT)
#	cp -r config.searchd/* $(DESTDIR)/etc/$(SEARCH_ENGINE_DIR)/conf.d/$(SEARCH_ENGINE_PROJECT)/
#	chown -R manticore:manticore $(DESTDIR)/etc/$(SEARCH_ENGINE_DIR)/conf.d/$(SEARCH_ENGINE_PROJECT)/
	install -d $(PATH_PROJECT)/cache
	install -d $(PATH_PROJECT)/logs

# ------------------------------------------------
# Add the following 'help' target to your makefile, add help text after each target name starting with '\#\#'
# A category can be added with @category
GREEN  := $(shell tput -Txterm setaf 2)
YELLOW := $(shell tput -Txterm setaf 3)
WHITE  := $(shell tput -Txterm setaf 7)
RESET  := $(shell tput -Txterm sgr0)
HELP_ACTION = \
	%help; while(<>) { push @{$$help{$$2 // 'options'}}, [$$1, $$3] if /^([a-zA-Z\-_]+)\s*:.*\#\#(?:@([a-zA-Z\-]+))?\s(.*)$$/ }; \
	print "usage: make [target]\n\n"; for (sort keys %help) { print "${WHITE}$$_:${RESET}\n"; \
	for (@{$$help{$$_}}) { $$sep = " " x (32 - length $$_->[0]); print "  ${YELLOW}$$_->[0]${RESET}$$sep${GREEN}$$_->[1]${RESET}\n"; }; \
	print "\n"; }

# -eof-
