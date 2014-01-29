MW_INSTALL_PATH ?= ../..

# Flow files to analyze
ANALYZE=container.php Flow.php Resources.php includes/

# Extra files with some of the dependencies to reduce false positives from hhvm-wrapper
ANALYZE_EXTRA=../../includes/GlobalFunctions.php ../../includes/Defines.php ../../includes/api/ApiBase.php \
	../../includes/logging/LogFormatter.php ../../includes/context/ContextSource.php \
	../../includes/db/DatabaseUtility.php \
	../Echo/formatters/BasicFormatter.php ../Echo/formatters/NotificationFormatter.php


ee-flow:
	ssh ee-flow.pmtpa.wmflabs 'cd /srv/mediawiki/extensions/Flow && make master'
ee-flow-extra:
	ssh ee-flow-extra.pmtpa.wmflabs 'cd /vagrant/mediawiki/extensions/Flow && make master'
ee-flow-big:
	ssh ee-flow-big.pmtpa.wmflabs 'cd /srv/mediawiki/extensions/Flow && make master'
update-labs: ee-flow ee-flow-extra ee-flow-big

install-analyze:
	wget -O scripts/hhvm-wrapper.phar https://phar.phpunit.de/hhvm-wrapper.phar
	@which hhvm >/dev/null || which ${HHVM_HOME} >/dev/null || (echo Could not locate hhvm && false)

analyze:
	@test -f scripts/hhvm-wrapper.phar || (echo Run \`make install-analyze\` first && false)
	php scripts/hhvm-wrapper.phar analyze ${ANALYZE} ${ANALYZE_EXTRA}

master:
	git fetch
	@echo Here is what is new on origin/master:
	@git log HEAD..origin/master
	@echo Checkout and update master:
	git checkout master && git pull --ff-only
	@echo 'exit( ( $$wgFlowCluster === false && $$wgFlowDefaultWikiDb === false) ? 0 : 1 )' | php ../../maintenance/eval.php && echo Apply DB updates \(if any\) && php $(MW_INSTALL_PATH)/maintenance/update.php  --quick | sed -n '/^[^.]/p' || echo DB updates must be applied manually.
	@echo TODO Update Parsoid and restart it\? Other extensions\?
	@echo Run some tests\!\!\!

vagrant-browsertests:
	@vagrant ssh -- -X cd /srv/browsertests '&&' MEDIAWIKI_URL=http://localhost/wiki/ MEDIAWIKI_USER=Admin MEDIAWIKI_PASSWORD=vagrant bundle exec cucumber /vagrant/mediawiki/extensions/Flow/tests/browser/features/ -f pretty
