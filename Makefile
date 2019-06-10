TD=orders
PREFIX=.

help:

update-mo: po/de.mo

po/de.mo: po/de.po
	msgfmt po/de.po -opo/de.mo

install: po/de.mo
	install -d $(PREFIX)/locale/de/LC_MESSAGES
	install po/de.mo $(PREFIX)/locale/de/LC_MESSAGES
