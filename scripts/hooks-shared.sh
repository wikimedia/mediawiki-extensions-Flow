#
# Shared functionality of the Flow git hooks
#

realpath() {
	php -r "echo realpath('$1'), \"\\n\";"
}

is_vagrant() {
    DEST='.'
    while [ "$(realpath $DEST)" != "/" ]; do
        if [ -f $DEST/Vagrantfile ]; then
            return 0;
        fi
        DEST="$DEST/.."
    done
    return 1
}

make() {
    if is_vagrant; then
        echo 'git hooks: Attempting to ssh into vagrant'
        vagrant ssh -- cd /vagrant/mediawiki/extensions/Flow '&&' /bin/echo 'git hooks: Running commands inside Vagrant' '&&' sudo -u www-data make $* || exit 1
    else
        /usr/bin/env make $* || exit 1
    fi
}

file_changed_in_commit() {
	git diff --name-only --cached | grep -P "$1" 2>&1 >/dev/null
}

file_changed_in_head() {
	git diff-tree --no-commit-id --name-only -r HEAD | grep -P "$1" 2>&1 >/dev/null
}

