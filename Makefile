TD=orders
PREFIX=.

help:

update-mo: po/de.mo

po/de.mo: po/de.po
	msgfmt po/de.po -opo/de.mo

po/de.po: po/orders.pot
	msgmerge -U po/de.po po/orders.pot

po/orders.pot: check-orders.sh
	xgettext -kGETTEXT -L Shell -F check-orders.sh -o po/orders.pot

install: po/de.mo
	install -d $(PREFIX)/locale/de/LC_MESSAGES
	install po/de.mo $(PREFIX)/locale/de/LC_MESSAGES/orders.mo
