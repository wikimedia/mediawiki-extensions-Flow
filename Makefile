MW_INSTALL_PATH ?= ../..

# Flow files to analyze
ANALYZE=container.php Flow.php Resources.php includes/

# Extra files with some of the dependencies to reduce false positives from hhvm-wrapper
ANALYZE_EXTRA=../../includes/GlobalFunctions.php ../../includes/Defines.php ../../includes/api/ApiBase.php \
	../../includes/logging/LogFormatter.php ../../includes/context/ContextSource.php \
	../../includes/db/DatabaseUtility.php \
	../Echo/formatters/BasicFormatter.php ../Echo/formatters/NotificationFormatter.php


###
# Labs maintenance
###
ee-flow:
	ssh ee-flow.eqiad.wmflabs 'cd /srv/mediawiki/extensions/Flow && make master'
ee-flow-extra:
	ssh ee-flow-extra.eqiad.wmflabs 'cd /vagrant/mediawiki/extensions/Flow && make master'
# Used to be ee-flow-big, not so big any more
ee-flow-extra2:
	ssh ee-flow-extra2.eqiad.wmflabs 'cd /srv/mediawiki/extensions/Flow && make master'
update-labs: ee-flow ee-flow-extra ee-flow-extra2

###
# Meta stuff
###
installhooks:
	ln -sf ${PWD}/scripts/pre-commit .git/hooks/pre-commit
	ln -sf ${PWD}/scripts/pre-review .git/hooks/pre-review

###
# Lints
###
lint: jshint phplint checkless

phplint:
	@find ./ -type f -iname '*.php' | xargs -P 12 -L 1 php -l

nodecheck:
	@which npm > /dev/null && npm install \
		|| (echo "You need to install Node.JS! See http://nodejs.org/" && false)

jshint: nodecheck
	@node_modules/.bin/jshint modules/* --config .jshintrc

checkless:
	@php ../../maintenance/checkLess.php

###
# Testing
###
phpunit:
	cd ${MW_INSTALL_PATH}/tests/phpunit && php phpunit.php --configuration ${MW_INSTALL_PATH}/extensions/Flow/tests/flow.suite.xml --group=Flow

qunit:
	@echo TODO: qunit tests

vagrant-browsertests:
	@vagrant ssh -- -X cd /srv/browsertests '&&' MEDIAWIKI_URL=http://localhost/wiki/ MEDIAWIKI_USER=Admin MEDIAWIKI_PASSWORD=vagrant bundle exec cucumber /vagrant/mediawiki/extensions/Flow/tests/browser/features/ -f pretty

check-i18n:
	@php scripts/check-i18n.php

###
# Static analysis
###
install-analyze-hhvm:
	wget -O scripts/hhvm-wrapper.phar https://phar.phpunit.de/hhvm-wrapper.phar
	@which hhvm >/dev/null || which ${HHVM_HOME} >/dev/null || (echo Could not locate hhvm && false)

analyze-hhvm:
	@test -f scripts/hhvm-wrapper.phar || (echo Run \`make install-analyze\` first && false)
	php scripts/hhvm-wrapper.phar analyze ${ANALYZE} ${ANALYZE_EXTRA}

analyze-phpstorm:
	@scripts/analyze-phpstorm.sh

analyze: analyze-hhvm analyze-phpstorm

###
# Compile lightncandy templates
###
compile-lightncandy:
	make -C handlebars all

###
# Update this repository
###
master:
	git fetch
	@echo Here is what is new on origin/master:
	@git log HEAD..origin/master
	@echo Checkout and update master:
	git checkout master && git pull --ff-only
	@echo 'exit( ( $$wgFlowCluster === false && $$wgFlowDefaultWikiDb === false) ? 0 : 1 )' | php ../../maintenance/eval.php && echo Apply DB updates \(if any\) && php $(MW_INSTALL_PATH)/maintenance/update.php  --quick | sed -n '/^[^.]/p' || echo DB updates must be applied manually.
	@echo TODO Update Parsoid and restart it\? Other extensions\?
	@echo Run some tests\!\!\!

