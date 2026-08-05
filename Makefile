DOCKER := docker

IMAGE = $(file < docker/IMAGE)
TAG = $(IMAGE):$(file < VERSION)

REGISTRY = aatf

all: build

build:
	$(DOCKER) login \
		--username wafbot \
		--password $(DOCKER_PASSWORD)
	$(DOCKER) buildx build \
		--no-cache \
		--platform linux/arm64,linux/amd64 \
		--push \
		--tag $(REGISTRY)/$(TAG) \
		--tag $(REGISTRY)/$(IMAGE):latest .

run: build
	$(DOCKER) run -p 60000:8080 -it --entrypoint /bin/bash $(TAG)

test: run

deploy:
	go run ./scripts/deploy.go
