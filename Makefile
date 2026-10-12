DOCKER := docker

IMAGE = $(file < docker/IMAGE)
TAG = $(IMAGE):$(file < VERSION)

REGISTRY = aatf

ARCH ?= amd64
PLATFORM ?= linux/$(ARCH)
PUSH ?= --push

all: build

build:
	$(DOCKER) buildx build \
		--file docker/Dockerfile \
		--no-cache \
		--platform $(PLATFORM) \
		$(PUSH) \
		--tag $(REGISTRY)/$(TAG)-$(ARCH) \
		--tag $(REGISTRY)/$(IMAGE):latest-$(ARCH) .

manifest:
	$(DOCKER) buildx imagetools create \
		--tag $(REGISTRY)/$(TAG) \
		--tag $(REGISTRY)/$(IMAGE):latest \
		$(REGISTRY)/$(TAG)-amd64 \
		$(REGISTRY)/$(TAG)-arm64

build-local:
	$(DOCKER) buildx build \
		--file docker/Dockerfile \
		--no-cache \
		--tag $(REGISTRY)/$(TAG)-$(ARCH) \
		--tag $(REGISTRY)/$(IMAGE):latest-$(ARCH) .

run:
	$(DOCKER) run -p 60000:8080 $(REGISTRY)/$(TAG)-$(ARCH)

build-and-run: build-local run

test:
	$(DOCKER) run -p 60000:8080 -it --entrypoint /bin/bash $(REGISTRY)/$(TAG)-$(ARCH)

build-and-test: build-local test

deploy:
	go run ./scripts/deploy.go
