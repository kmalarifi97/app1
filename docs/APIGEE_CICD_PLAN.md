# CI/CD Pipeline: Laravel App to GKE with Apigee Proxy

## Goal
Set up automated deployment where git push triggers:
1. Build Docker image of Laravel app
2. Deploy to GKE (Google Kubernetes Engine)
3. Update Apigee API proxy to route to the deployed service

## Prerequisites Check
- Create new GCP project (or use existing `arabic-letters-board`)
- Enable required APIs: GKE, Artifact Registry, Cloud Build, Apigee
- Set up Apigee organization and environment (if not exists)

## Implementation Steps

### Phase 1: Containerize Laravel App
1. Create Dockerfile for app1
2. Create docker-compose.yml for local testing
3. Test container locally

### Phase 2: GCP Setup
1. Create new GCP project for this demo (or use existing)
2. Enable APIs:
   - container.googleapis.com (GKE)
   - artifactregistry.googleapis.com (Docker registry)
   - cloudbuild.googleapis.com (CI/CD)
   - apigee.googleapis.com (API Gateway)
3. Create GKE cluster
4. Create Artifact Registry repository for Docker images

### Phase 3: Apigee Setup
1. Provision Apigee instance (if needed)
2. Create API proxy configuration
3. Configure target endpoint to GKE service
4. Set up routing rules

### Phase 4: CI/CD Pipeline
1. Create cloudbuild.yaml for automated builds
2. Create Kubernetes deployment manifests (deployment.yaml, service.yaml)
3. Set up Cloud Build trigger on git push
4. Create Apigee deployment script
5. Test end-to-end: git push → build → deploy → Apigee update

### Phase 5: Documentation
1. Create README with testing instructions
2. Document API endpoints accessible via Apigee
3. Add troubleshooting guide

## Architecture Overview

```
Developer → Git Push → Cloud Build Trigger
                          ↓
                    Build Docker Image
                          ↓
                    Push to Artifact Registry
                          ↓
                    Deploy to GKE
                          ↓
                    Update Apigee Proxy
                          ↓
                    API Available via Apigee Gateway
```

## API Endpoints (via Apigee)

Once deployed, the following endpoints will be available through Apigee:

- `GET https://[apigee-host]/app1/api/data` - Retrieve sample data
- `POST https://[apigee-host]/app1/api/data` - Send data to app1

## Environment Information

- **GCP Account:** kmalarifi97@gmail.com
- **Current Project:** arabic-letters-board
- **Project Number:** 946467464378
- **gcloud SDK:** Installed at ~/google-cloud-sdk/bin/gcloud

## Next Steps

1. Decide whether to use existing project or create new one
2. Check if Apigee is already provisioned
3. Create Dockerfile and test locally
4. Set up GKE cluster
5. Configure Cloud Build
6. Create Apigee proxy
7. Test the full pipeline
