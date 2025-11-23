# GKE + Apigee Deployment Steps

This document records all commands and steps used to deploy the Laravel applications to Google Kubernetes Engine with Apigee API Gateway.

---

## Phase 1: Environment Setup

### 1. Verify gcloud Installation

**Command:**
```bash
export PATH="$HOME/google-cloud-sdk/bin:$PATH"
gcloud --version
```

**Actual Output:**
```
Google Cloud SDK 547.0.0
bq 2.1.25
bundled-python3-unix 3.13.7
core 2025.11.07
gcloud-crc32c 1.0.0
gsutil 5.35
```

**Verification:**
```bash
which gcloud
# Output: /home/kalarifi/google-cloud-sdk/bin/gcloud
```

**Purpose:** Confirm gcloud CLI is installed and accessible.

**Status:** ✅ Completed

---

### 2. Authenticate with GCP

**Command:**
```bash
gcloud auth login
```

**Note:** Already authenticated

**Verification:**
```bash
gcloud auth list
```

**Actual Output:**
```
Credentialed Accounts
ACTIVE  ACCOUNT
*       kmalarifi97@gmail.com
```

**Purpose:** Authenticate your Google Cloud account to manage GCP resources.

**Status:** ✅ Completed (Already authenticated)

---

### 3. Create or Select GCP Project

**Command:**
```bash
gcloud projects create laravel-apigee-demo --name="Laravel Apigee Demo"
gcloud config set project laravel-apigee-demo
```

**Actual Output:**
```
Create in progress for [https://cloudresourcemanager.googleapis.com/v1/projects/laravel-apigee-demo].
Waiting for [operations/create_project.global.6371263570230016667] to finish...done.
Updated property [core/project].
```

**Verification:**
```bash
gcloud projects describe laravel-apigee-demo
```

**Project Details:**
- **Project ID:** laravel-apigee-demo
- **Project Number:** 182204870872
- **Name:** Laravel Apigee Demo
- **Lifecycle State:** ACTIVE
- **Created:** 2025-11-22T10:06:38Z

**Purpose:** Set up or select the GCP project for deployment.

**Status:** ✅ Completed

---

### 4. Link Billing Account

**Command:**
```bash
gcloud billing accounts list
gcloud billing projects link laravel-apigee-demo --billing-account=01632C-D57787-6ABB05
```

**Actual Output:**
```
billingAccountName: billingAccounts/01632C-D57787-6ABB05
billingEnabled: true
name: projects/laravel-apigee-demo/billingInfo
projectId: laravel-apigee-demo
```

**Verification:**
```bash
gcloud billing projects describe laravel-apigee-demo
# billingEnabled: true ✅
```

**Purpose:** Enable billing for the project (required for GKE and Apigee).

**Status:** ✅ Completed

---

### 5. Enable Required APIs

**Command:**
```bash
gcloud services enable \
  container.googleapis.com \
  artifactregistry.googleapis.com \
  cloudbuild.googleapis.com \
  apigee.googleapis.com
```

**Actual Output:**
```
Operation "operations/acf.p2-182204870872-048bd49d-5e36-4cac-b065-d3ce3848db3f" finished successfully.
```

**Verification:**
```bash
gcloud services list --enabled --filter="name:(container OR artifactregistry OR cloudbuild OR apigee)"
```

**Enabled APIs:**
- ✅ apigee.googleapis.com - Apigee API
- ✅ artifactregistry.googleapis.com - Artifact Registry API
- ✅ cloudbuild.googleapis.com - Cloud Build API
- ✅ container.googleapis.com - Kubernetes Engine API

**Purpose:** Enable GKE, Artifact Registry, Cloud Build, and Apigee APIs.

**Status:** ✅ Completed

---

## Phase 2: Container Setup

### 6. Create Dockerfile for App1

**Files Created:**
- `app1/Dockerfile` - PHP 8.2 with Apache
- `app1/.dockerignore` - Exclude unnecessary files
- `app1/docker-compose.yml` - Local testing setup

**Verification:**
```bash
docker images | grep app1
```

**Actual Output:**
```
app1-test    latest    371949dae9c1   8 minutes ago   585MB
```

**Purpose:** Containerize the Laravel application for deployment.

**Status:** ✅ Completed

---

### 7. Test Container Locally

**Verification:**
```bash
curl http://app1.lndo.site:8000/api/data
curl -X POST http://app1.lndo.site:8000/api/data -H "Content-Type: application/json" -d '{"test":"data"}'
```

**Actual Output:**
```json
GET: {"app":"app1","endpoint":"GET /api/data","message":"Data retrieved successfully",...}
POST: {"app":"app1","endpoint":"POST /api/data","message":"Data received successfully",...}
```

**Purpose:** Verify the container works correctly before deploying.

**Status:** ✅ Completed

---

## Phase 3: Artifact Registry Setup

### 8. Create Artifact Registry Repository

**Command:**
```bash
gcloud artifacts repositories create laravel-apps \
  --repository-format=docker \
  --location=us-central1 \
  --description="Laravel applications for Apigee demo"
```

**Actual Output:**
```
Created repository [laravel-apps].
```

**Repository Details:**
- **URI:** us-central1-docker.pkg.dev/laravel-apigee-demo/laravel-apps
- **Format:** DOCKER
- **Location:** us-central1
- **Created:** 2025-11-22T10:55:05Z

**Purpose:** Create a Docker registry to store container images.

**Status:** ✅ Completed

---

### 9. Configure Docker Authentication

**Command:**
```bash
gcloud auth configure-docker us-central1-docker.pkg.dev
```

**Actual Output:**
```
Docker configuration file updated.
```

**Verification:**
```bash
cat ~/.docker/config.json
# Shows: "us-central1-docker.pkg.dev": "gcloud"
```

**Purpose:** Allow Docker to push images to Artifact Registry.

**Status:** ✅ Completed

---

### 10. Build and Push Docker Image

**Command:**
```bash
docker tag app1-test us-central1-docker.pkg.dev/laravel-apigee-demo/laravel-apps/app1:v1
export PATH="$HOME/google-cloud-sdk/bin:$PATH"
docker push us-central1-docker.pkg.dev/laravel-apigee-demo/laravel-apps/app1:v1
```

**Actual Output:**
```
v1: digest: sha256:957db3a85966b9b0011bbe312f8e446a8c9bdfdaaae253e596dad17ba7d9b4e9 size: 5536
```

**Verification:**
```bash
gcloud artifacts docker images list us-central1-docker.pkg.dev/laravel-apigee-demo/laravel-apps
```

**Image Details:**
- **Image:** us-central1-docker.pkg.dev/laravel-apigee-demo/laravel-apps/app1
- **Tag:** v1
- **Size:** 211.6 MB
- **Digest:** sha256:957db3a85966b9b0011bbe312f8e446a8c9bdfdaaae253e596dad17ba7d9b4e9

**Note:** Export PATH with gcloud before pushing to ensure docker-credential-gcloud helper works

**Purpose:** Build and upload container image to registry.

**Status:** ✅ Completed

---

## Phase 4: GKE Cluster Setup

### 11. Create GKE Cluster

**Note:** Cluster already exists

**Verification:**
```bash
gcloud container clusters list
```

**Cluster Details:**
- **Name:** laravel-cluster
- **Location:** us-central1-a
- **Master IP:** 34.170.225.91
- **Master Version:** 1.33.5-gke.1201000
- **Machine Type:** e2-medium
- **Node Count:** 2
- **Status:** RUNNING ✅

**UI Verification:** https://console.cloud.google.com/kubernetes/list?project=laravel-apigee-demo

**Purpose:** Create Kubernetes cluster to host the applications.

**Status:** ✅ Completed

---

### 12. Get Cluster Credentials

**Prerequisites:**
```bash
# Install kubectl and auth plugin
gcloud components install kubectl gke-gcloud-auth-plugin
```

**Command:**
```bash
gcloud container clusters get-credentials laravel-cluster --zone=us-central1-a
```

**Verification:**
```bash
kubectl cluster-info
kubectl get nodes
```

**Actual Output:**
```
Kubernetes control plane is running at https://34.170.225.91
NAME                                             STATUS   ROLES    AGE   VERSION
gke-laravel-cluster-default-pool-e7f65316-96tr   Ready    <none>   32m   v1.33.5-gke.1201000
gke-laravel-cluster-default-pool-e7f65316-z23g   Ready    <none>   32m   v1.33.5-gke.1201000
```

**Purpose:** Configure kubectl to interact with the cluster.

**Status:** ✅ Completed

---

## Phase 5: Deploy to Kubernetes

### 13. Create Kubernetes Deployment Manifest

**File:** `app1/k8s/deployment.yaml`

**Content:** Deployment with 2 replicas, resource limits, health probes

**Purpose:** Define how the application should run in Kubernetes.

**Status:** ✅ Completed

---

### 14. Create Kubernetes Service Manifest

**File:** `app1/k8s/service.yaml`

**Content:** LoadBalancer service exposing port 80

**Purpose:** Expose the application within and outside the cluster.

**Status:** ✅ Completed

---

### 15. Deploy Application to GKE

**Command:**
```bash
kubectl apply -f app1/k8s/deployment.yaml
kubectl apply -f app1/k8s/service.yaml
```

**Actual Output:**
```
deployment.apps/app1 created
service/app1-service created
```

**Verification:**
```bash
kubectl get deployments
kubectl get pods
kubectl get services
```

**Deployment Status:**
- **Deployment:** app1 (1/2 ready)
- **Pods:** 1 Running, 1 Pending
- **Service:** app1-service (LoadBalancer)
- **External IP:** 34.171.31.224

**Purpose:** Deploy the Laravel application to Kubernetes.

**Status:** ✅ Completed

---

### 16. Test Application on GKE

**Command:**
```bash
EXTERNAL_IP=34.171.31.224
curl http://${EXTERNAL_IP}/api/data
curl -X POST http://${EXTERNAL_IP}/api/data -H "Content-Type: application/json" -d '{"test":"from GKE"}'
```

**Actual Output:**
```json
GET: {"app":"app1","endpoint":"GET /api/data","message":"Data retrieved successfully",...}
POST: {"app":"app1","endpoint":"POST /api/data","message":"Data received successfully","received_data":{"test":"from GKE"},...}
```

**Purpose:** Verify the application is running correctly on GKE.

**Status:** ✅ Completed

---

## Phase 6: Apigee Setup

### 17. Provision Apigee Instance

**UI Guidance:**
1. Navigate to: https://console.cloud.google.com/apigee
2. Click "Provision Apigee"
3. Select region and configuration
4. Wait for provisioning (can take 30-60 minutes)

**Verification:**
```bash
gcloud apigee organizations list
gcloud apigee organizations describe [ORG_NAME]
```

**Purpose:** Set up Apigee API Gateway instance.

**Status:** ⏳ Pending

---

### 18. Create Apigee Environment

**Command:**
```bash
gcloud apigee environments create dev \
  --organization=[ORG_NAME]
```

**Verification:**
```bash
gcloud apigee environments list --organization=[ORG_NAME]
```

**UI Verification:** https://console.cloud.google.com/apigee/environments

**Purpose:** Create an environment for API proxies.

**Status:** ⏳ Pending

---

### 19. Create API Proxy Bundle

**File:** `app1/apigee/apiproxy/app1-proxy.xml`

**Verification:** XML files created in proper Apigee bundle structure

**Purpose:** Define API proxy configuration for routing.

**Status:** ⏳ Pending

---

### 20. Deploy API Proxy to Apigee

**Command:**
```bash
# Using apigeecli or apigeetool
apigeecli apis create bundle \
  -f app1/apigee/apiproxy \
  -n app1-proxy \
  --org=[ORG_NAME] \
  --env=dev
```

**Verification:**
```bash
apigeecli apis list --org=[ORG_NAME]
apigeecli apis get --name=app1-proxy --org=[ORG_NAME]
```

**UI Verification:** https://console.cloud.google.com/apigee/proxies

**Purpose:** Deploy the API proxy to Apigee.

**Status:** ⏳ Pending

---

### 21. Test API Through Apigee

**Command:**
```bash
curl https://[APIGEE_HOST]/app1/api/data
curl -X POST https://[APIGEE_HOST]/app1/api/data \
  -H "Content-Type: application/json" \
  -d '{"test": "data"}'
```

**Expected Output:** JSON responses from the API through Apigee

**Purpose:** Verify API is accessible through Apigee Gateway.

**Status:** ⏳ Pending

---

## Phase 7: CI/CD Pipeline

### 22. Create Cloud Build Configuration

**File:** `app1/cloudbuild.yaml`

**Verification:** File created with valid YAML syntax

**Purpose:** Define automated build and deployment steps.

**Status:** ⏳ Pending

---

### 23. Create Service Account for Cloud Build

**Command:**
```bash
gcloud iam service-accounts create cloud-build-deployer \
  --display-name="Cloud Build Deployer"
```

**Verification:**
```bash
gcloud iam service-accounts list | grep cloud-build-deployer
```

**Purpose:** Create service account for automated deployments.

**Status:** ⏳ Pending

---

### 24. Grant Service Account Permissions

**Command:**
```bash
PROJECT_ID=$(gcloud config get-value project)

gcloud projects add-iam-policy-binding ${PROJECT_ID} \
  --member="serviceAccount:cloud-build-deployer@${PROJECT_ID}.iam.gserviceaccount.com" \
  --role="roles/container.developer"

gcloud projects add-iam-policy-binding ${PROJECT_ID} \
  --member="serviceAccount:cloud-build-deployer@${PROJECT_ID}.iam.gserviceaccount.com" \
  --role="roles/cloudbuild.builds.editor"

gcloud projects add-iam-policy-binding ${PROJECT_ID} \
  --member="serviceAccount:cloud-build-deployer@${PROJECT_ID}.iam.gserviceaccount.com" \
  --role="roles/artifactregistry.writer"
```

**Verification:**
```bash
gcloud projects get-iam-policy ${PROJECT_ID} \
  --flatten="bindings[].members" \
  --filter="bindings.members:cloud-build-deployer@${PROJECT_ID}.iam.gserviceaccount.com"
```

**Purpose:** Grant permissions for automated deployments.

**Status:** ⏳ Pending

---

### 25. Create Cloud Build Trigger

**Command:**
```bash
gcloud builds triggers create github \
  --repo-name=[REPO_NAME] \
  --repo-owner=[GITHUB_USERNAME] \
  --branch-pattern="^main$" \
  --build-config=app1/cloudbuild.yaml \
  --service-account="projects/${PROJECT_ID}/serviceAccounts/cloud-build-deployer@${PROJECT_ID}.iam.gserviceaccount.com"
```

**Verification:**
```bash
gcloud builds triggers list
```

**UI Verification:** https://console.cloud.google.com/cloud-build/triggers

**Purpose:** Automatically trigger builds on git push.

**Status:** ⏳ Pending

---

### 26. Test CI/CD Pipeline

**Command:**
```bash
cd app1
git add .
git commit -m "Test CI/CD pipeline"
git push origin main
```

**Verification:**
```bash
gcloud builds list --limit=5
gcloud builds log [BUILD_ID]
```

**UI Verification:** https://console.cloud.google.com/cloud-build/builds

**Purpose:** Test the complete automated deployment pipeline.

**Status:** ⏳ Pending

---

## Notes

- Replace `[PROJECT_ID]`, `[ORG_NAME]`, `[APIGEE_HOST]`, etc. with actual values
- All commands assume you're running from `/home/kalarifi/projects/dummy-projects/`
- Keep service account keys secure and never commit to git
- Monitor costs at: https://console.cloud.google.com/billing

## Useful Commands

**Check Project Info:**
```bash
gcloud config list
gcloud projects describe $(gcloud config get-value project)
```

**Monitor Kubernetes:**
```bash
kubectl get all
kubectl logs -f deployment/app1
kubectl describe pod [POD_NAME]
```

**Clean Up Resources:**
```bash
kubectl delete -f app1/k8s/
gcloud container clusters delete laravel-cluster --zone=us-central1-a
gcloud artifacts repositories delete laravel-apps --location=us-central1
```
