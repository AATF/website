DOCKER := docker

IMAGE = $(file < docker/IMAGE)
TAG = $(IMAGE):$(file < VERSION)

REGISTRY = aatf

all: build

build:
	$(DOCKER) buildx build \
		--file docker/Dockerfile \
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
