MW_INSTALL_PATH ?= ../..

ee-flow:
	ssh ee-flow.pmtpa.wmflabs 'cd /srv/mediawiki/extensions/Flow && make'

master:
	git fetch
	echo Here is what is new on origin/master
	# This doesn't work, despite what gitrevisions(7) says.
	git log HEAD..origin/master
	echo Get master
	git checkout master && git pull --ff-only
	echo Update Parsoid and restart it\? Other extensions\?
	echo Run some tests\!\!\!
