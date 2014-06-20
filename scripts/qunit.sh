#!/usr/bin/env bash
echo "Running QUnit tests..."
if command -v phantomjs > /dev/null ; then
  URL=${MEDIAWIKI_URL:-"http://127.0.0.1:80/w/index.php/"}
  echo "Using $URL as a development environment host."
  echo "Please ensure \$wgEnableJavaScriptTest = true; in your LocalSettings.php"
  echo "To specify a different host set MEDIAWIKI_URL environment variable"
  echo '(e.g. by running "export MEDIAWIKI_URL=http://127.0.0.1:80/w/index.php/")'
  phantomjs tests/externals/phantomjs-qunit-runner.js "${URL}Special:JavaScriptTest/qunit?filter=ext.flow"
else
  echo "You need to install PhantomJS to run QUnit tests in terminal!"
  echo "See http://phantomjs.org/"
  exit 1
fi
