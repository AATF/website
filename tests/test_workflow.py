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



class TestDeployWorkflow(unittest.TestCase):
    @classmethod
    def setUpClass(cls):
        cls.repo_root = Path(__file__).resolve().parent.parent
        cls.workflow_path = cls.repo_root / ".github" / "workflows" / "deploy.yaml"

        with open(cls.workflow_path, "r", encoding="utf-8") as f:
            cls.workflow = yaml.safe_load(f)

    def test_workflow_exists(self):
        self.assertTrue(self.workflow_path.exists())

    def test_workflow_run_trigger_and_build_image_dependency(self):
        triggers = self.workflow.get("on") or self.workflow.get(True) or {}
        self.assertIn("workflow_run", triggers)
        workflow_run = triggers["workflow_run"]
        self.assertIn("build image", workflow_run.get("workflows", []))
        self.assertIn("completed", workflow_run.get("types", []))

    def test_job_conclusion_check(self):
        deploy_job = self.workflow["jobs"]["deploy"]
        condition = deploy_job.get("if", "")
        self.assertIn("workflow_run.conclusion == 'success'", condition)

    def test_permissions_has_id_token_write(self):
        permissions = self.workflow.get("permissions", {})
        self.assertEqual(permissions.get("id-token"), "write")
        self.assertEqual(permissions.get("contents"), "read")

    def test_tailscale_step_exists(self):
        steps = self.workflow["jobs"]["deploy"]["steps"]
        tailscale_steps = [s for s in steps if "tailscale/github-action" in s.get("uses", "")]
        self.assertEqual(len(tailscale_steps), 1)

    def test_aws_oidc_step_exists(self):
        steps = self.workflow["jobs"]["deploy"]["steps"]
        aws_steps = [s for s in steps if "aws-actions/configure-aws-credentials" in s.get("uses", "")]
        self.assertEqual(len(aws_steps), 1)
        with_cfg = aws_steps[0].get("with", {})
        self.assertIn("role-to-assume", with_cfg)

    def test_kubeconfig_step_exists(self):
        steps = self.workflow["jobs"]["deploy"]["steps"]
        kubeconfig_steps = [s for s in steps if "aws eks update-kubeconfig" in s.get("run", "")]
        self.assertEqual(len(kubeconfig_steps), 1)

    def test_kubectl_rollout_restart_command(self):
        steps = self.workflow["jobs"]["deploy"]["steps"]
        rollout_steps = [s for s in steps if "kubectl -n website rollout restart website-aatf-common" in s.get("run", "")]
        self.assertEqual(len(rollout_steps), 1)


if __name__ == "__main__":
    unittest.main()

