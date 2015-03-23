
include wikidata.mk
# XMLFIL=$(XMLDIR)/wikidatawiki-$(WIKIDATADATE)-pages-articles.xml
include ../ny_wikidata/arkiv/wikidata.mk
JSONFIL=../ny_wikidata/arkiv/$(WIKIDATADATE).json.gz

ALL=ana_wd_commons.out p373_missing.out p373_missing.rap p373_diff.out p935_missing.out p935_missing.rap p935_diff.out

all: $(ALL)

ana_wd_commons.out: $(JSONFIL) ana_wd_commons.php
	zcat $(JSONFIL) | sed -e "s/,$$//" | php ana_wd_commons.php > $@

p373_missing.out: ana_wd_commons.out
	grep commonscat_p373_missing ana_wd_commons.out > $@

p373_diff.out: ana_wd_commons.out
	grep commonscat_p373_diff ana_wd_commons.out > $@

p373_missing.rap: ana_wd_commons.out
	echo "Misssion {{p|373}} - link to commonscategory" > $@
	grep commonscat_p373_missing ana_wd_commons.out | awk '{ print "* [[q" $$2 "]] - [[commons:" $$3 "]]" }' | tr '_' ' ' >> $@

p935_missing.out: ana_wd_commons.out
	grep commons_p935_missing ana_wd_commons.out > $@

p935_diff.out: ana_wd_commons.out
	grep commons_p935_diff ana_wd_commons.out > $@

p935_missing.rap: ana_wd_commons.out
	grep commons_p935_missing ana_wd_commons.out | awk '{ print "* [[q" $$2 "]] - [[commons:" $$3 "]]" }' | tr '_' ' ' > $@
