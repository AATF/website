import unittest
import yaml
from pathlib import Path


class TestBuildImageWorkflow(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.repo_root = Path(__file__).resolve().parent.parent
        cls.workflow_path = cls.repo_root / ".github" / "workflows" / "build-image.yaml"
        cls.makefile_path = cls.repo_root / "Makefile"

        with open(cls.workflow_path, "r", encoding="utf-8") as f:
            cls.workflow = yaml.safe_load(f)

        with open(cls.makefile_path, "r", encoding="utf-8") as f:
            cls.makefile = f.read()

    def test_workflow_has_build_and_manifest_jobs(self):
        jobs = self.workflow.get("jobs", {})
        self.assertIn("build", jobs)
        self.assertIn("manifest", jobs)

    def test_build_job_matrix_runners_and_platforms(self):
        build_job = self.workflow["jobs"]["build"]
        matrix = build_job.get("strategy", {}).get("matrix", {})
        include = matrix.get("include", [])
        self.assertEqual(len(include), 2)

        runners = {item["runner"]: item for item in include}
        self.assertIn("ubuntu-latest", runners)
        self.assertEqual(runners["ubuntu-latest"]["platform"], "linux/amd64")
        self.assertEqual(runners["ubuntu-latest"]["arch"], "amd64")

        self.assertIn("ubuntu-24.04-arm", runners)
        self.assertEqual(runners["ubuntu-24.04-arm"]["platform"], "linux/arm64")
        self.assertEqual(runners["ubuntu-24.04-arm"]["arch"], "arm64")

        self.assertEqual(build_job.get("runs-on"), "${{ matrix.runner }}")

    def test_manifest_job_configuration(self):
        manifest_job = self.workflow["jobs"]["manifest"]
        self.assertEqual(manifest_job.get("needs"), "build")
        self.assertEqual(manifest_job.get("runs-on"), "ubuntu-latest")

    def test_makefile_has_build_and_manifest_targets(self):
        self.assertIn("build:", self.makefile)
        self.assertIn("manifest:", self.makefile)
        self.assertIn("imagetools create", self.makefile)
        self.assertIn("ARCH ?= amd64", self.makefile)
        self.assertIn("PLATFORM ?= linux/$(ARCH)", self.makefile)


if __name__ == "__main__":
    unittest.main()

