all:
	$(error "select a target")

ci:
	cd plugins/hphp && make CI=1
