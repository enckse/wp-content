PLUGIN_VERSION     := 1.8.6
PLUGIN_JS_VERSION  := 1.8.2
PLUGIN_CSS_VERSION := 1.0.0
PLUGIN_JS_HASH     := 91459f3
PLUGIN_CSS_HASH    := d7a8720
PLUGIN_HASH        := b470406
NEED_FILES         :=
HAVE_HASH          :=
NEED_HASH          :=
HASH_NAME          :=
CI                 := 0
ifneq ($(NEED_FILES),)
	NEED_HASH      := $(shell sha256sum *.$(NEED_FILES) | sort | sha256sum | cut -c 1-7)
endif

NAME     := hphp
TARGET   := target/
ZIP      := $(NAME)-$(PLUGIN_VERSION).zip
ARTIFACT := $(TARGET)$(ZIP)
WORKDIR  := $(TARGET)$(NAME)
EXT      :=

all: setup check $(ARTIFACT)

setup:
	@mkdir -p $(WORKDIR)

clean:
	@rm -rf $(TARGET)

$(ARTIFACT): *.*
	@make --no-print-directory _minify EXT=css
	@make --no-print-directory _minify EXT=js
	@sed "s/{VERSION}/$(PLUGIN_VERSION)/g" hphp.php | \
		sed "s/{CSS_VERSION}/$(PLUGIN_CSS_VERSION)/g" | \
		sed "s/{JS_VERSION}/$(PLUGIN_JS_VERSION)/g" > $(WORKDIR)/hphp.php
	@cd $(TARGET) && zip $(ZIP) hphp/*
	@echo "zip generated"

_minify:
	@for f in $(shell ls *.$(EXT)); do echo "minify: $$f"; minify -q -o $(WORKDIR)/$$f $$f; done

check:
	@make --no-print-directory _check_hash HAVE_HASH="$(PLUGIN_HASH)" NEED_FILES="*" HASH_NAME=plugin
	@make --no-print-directory _check_hash HAVE_HASH="$(PLUGIN_CSS_HASH)" NEED_FILES="css" HASH_NAME=css
	@make --no-print-directory _check_hash HAVE_HASH="$(PLUGIN_JS_HASH)" NEED_FILES="js" HASH_NAME=js


_check_hash:
ifeq ($(NEED_HASH),)
	$(error no hash generated to check)
endif
ifneq ($(NEED_HASH),$(HAVE_HASH))
	$(warning )
	$(warning ===============)
	$(warning WARN: found hash $(NEED_HASH) for $(HASH_NAME), version update required)
	$(warning ===============)
	$(warning )
ifeq ($(CI),1)
	$(error hash check failed)
endif
endif
	@exit 0
