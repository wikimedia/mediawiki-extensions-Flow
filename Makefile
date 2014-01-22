MW_INSTALL_PATH ?= ../..

ee-flow:
	ssh ee-flow.pmtpa.wmflabs 'cd /srv/mediawiki/extensions/Flow && make'

master:
	git fetch
	@echo Here is what is new on origin/master:
	@git log HEAD..origin/master
	@echo Checkout and update master:
	git checkout master && git pull --ff-only
	@echo 'exit( ( $$wgFlowCluster === false && $$wgFlowDefaultWikiDb === false) ? 0 : 1 )' | php ../../maintenance/eval.php && echo Apply DB updates \(if any\) && php $(MW_INSTALL_PATH)/maintenance/update.php  --quick | sed -n '/^[^.]/p' || echo DB updates must be applied manually.
	@echo TODO Update Parsoid and restart it\? Other extensions\?
	@echo Run some tests\!\!\!
