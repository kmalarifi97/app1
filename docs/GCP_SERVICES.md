# GCP Services Used in Deployment

This document lists all Google Cloud Platform services required for the Laravel + GKE + Apigee deployment.

---

## Core Services (API Enablement Required)

### 1. Container Registry (Artifact Registry)
- **API:** `artifactregistry.googleapis.com`
- **Purpose:** Store Docker container images
- **Usage:** Houses the Laravel application Docker images before deployment to GKE
- **Components:**
  - Docker image repositories
  - Image versioning and tagging
  - Vulnerability scanning
  - Access control and permissions

### 2. Google Kubernetes Engine (GKE)
- **API:** `container.googleapis.com`
- **Purpose:** Managed Kubernetes cluster hosting
- **Usage:** Runs the Laravel application containers with auto-scaling and load balancing
- **Components:**
  - Kubernetes clusters
  - Node pools (compute instances)
  - Workload management (Deployments, Pods)
  - Services and Load Balancers
  - Auto-scaling capabilities
  - Cluster monitoring and logging

### 3. Cloud Build
- **API:** `cloudbuild.googleapis.com`
- **Purpose:** Automated CI/CD pipeline
- **Usage:** Builds Docker images and deploys to GKE automatically on git push
- **Components:**
  - Build triggers (GitHub integration)
  - Build configurations (cloudbuild.yaml)
  - Build history and logs
  - Service account management
  - Substitution variables
  - Build steps and custom builders

### 4. Apigee API Gateway
- **API:** `apigee.googleapis.com`
- **Purpose:** API management and gateway
- **Usage:** Routes external API requests to the Laravel application running on GKE
- **Components:**
  - API Proxies (routing and policies)
  - Environments (dev, test, prod)
  - API Products (bundled APIs)
  - Developer Portal
  - Analytics and monitoring
  - Rate limiting and quotas
  - Security policies (OAuth, API keys)
  - Traffic management
  - Response caching

---

## Infrastructure Services (Auto-enabled)

### 5. Compute Engine
- **API:** `compute.googleapis.com`
- **Purpose:** Virtual machine infrastructure
- **Usage:** Underlying VMs for GKE nodes
- **Components:**
  - VM instances (e2-medium nodes)
  - Persistent disks
  - Network interfaces
  - Machine types and configurations
- **Note:** Auto-enabled when GKE is enabled

### 6. Cloud Resource Manager
- **API:** `cloudresourcemanager.googleapis.com`
- **Purpose:** Project and resource management
- **Usage:** Manages GCP project hierarchy and resources
- **Components:**
  - Project creation and configuration
  - Resource hierarchy
  - Project metadata
  - Organization policies
- **Note:** Auto-enabled for all GCP projects

### 7. Service Management
- **API:** `servicemanagement.googleapis.com`
- **Purpose:** API service configuration
- **Usage:** Manages enabled APIs and service configurations
- **Components:**
  - Service enablement/disablement
  - Service configurations
  - API quotas and limits
- **Note:** Auto-enabled for API management

### 8. Service Usage
- **API:** `serviceusage.googleapis.com`
- **Purpose:** API usage tracking
- **Usage:** Monitors and tracks API usage across services
- **Components:**
  - Usage metrics
  - Service status
  - Quota monitoring
- **Note:** Auto-enabled for all projects

---

## Security & Identity Services

### 9. Cloud IAM (Identity and Access Management)
- **API:** `iam.googleapis.com`
- **Purpose:** Authentication and authorization
- **Usage:** Manages service accounts, roles, and permissions
- **Components:**
  - Service accounts (cloud-build-deployer)
  - IAM roles and permissions
  - Policy bindings
  - Access control lists
  - Workload identity
- **Note:** Core service, always available

### 10. Cloud IAM Service Account Credentials
- **API:** `iamcredentials.googleapis.com`
- **Purpose:** Service account key management
- **Usage:** Generates and manages service account credentials
- **Components:**
  - Access tokens
  - ID tokens
  - Service account key creation
  - Credential rotation
- **Note:** Auto-enabled with IAM

---

## Networking Services

### 11. Compute Engine Networking
- **API:** Included in `compute.googleapis.com`
- **Purpose:** Virtual networking infrastructure
- **Usage:** Network connectivity for GKE clusters and load balancers
- **Components:**
  - VPC (Virtual Private Cloud)
  - Subnets
  - Firewall rules
  - Load balancers (for Kubernetes services)
  - External IP addresses
  - Network routes

### 12. Cloud DNS
- **API:** `dns.googleapis.com` (optional)
- **Purpose:** Domain name system management
- **Usage:** DNS management for Apigee custom domains (if used)
- **Components:**
  - Managed zones
  - DNS records
  - DNSSEC
- **Note:** Only needed for custom domain setup

---

## Monitoring & Logging Services

### 13. Cloud Logging (Stackdriver Logging)
- **API:** `logging.googleapis.com`
- **Purpose:** Log collection and analysis
- **Usage:** Captures logs from GKE, Cloud Build, and Apigee
- **Components:**
  - Log collection from all services
  - Log search and filtering
  - Log-based metrics
  - Log retention and archiving
- **Note:** Auto-enabled with GKE and other services

### 14. Cloud Monitoring (Stackdriver Monitoring)
- **API:** `monitoring.googleapis.com`
- **Purpose:** Performance monitoring and alerting
- **Usage:** Monitors GKE cluster health, resource usage, and application metrics
- **Components:**
  - Metrics collection
  - Dashboards
  - Alerting policies
  - Uptime checks
  - SLO monitoring
- **Note:** Auto-enabled with GKE

### 15. Cloud Trace
- **API:** `cloudtrace.googleapis.com`
- **Purpose:** Distributed tracing
- **Usage:** Traces requests across Apigee and GKE services
- **Components:**
  - Latency tracking
  - Request traces
  - Performance analysis
- **Note:** Optional, useful for debugging

---

## Storage & Database Services

### 16. Cloud Storage
- **API:** `storage.googleapis.com` (if used)
- **Purpose:** Object storage
- **Usage:** Stores build artifacts, logs, or application data (if configured)
- **Components:**
  - Storage buckets
  - Object versioning
  - Lifecycle management
  - Access control
- **Note:** Optional, depending on application needs

### 17. Container File System
- **API:** Part of GKE
- **Purpose:** Persistent storage for containers
- **Usage:** Provides persistent volumes for Kubernetes pods
- **Components:**
  - Persistent Volumes (PV)
  - Persistent Volume Claims (PVC)
  - Storage classes
- **Note:** Auto-available in GKE

---

## Developer Tools & APIs

### 18. Cloud Source Repositories (Optional)
- **API:** `sourcerepo.googleapis.com`
- **Purpose:** Git repository hosting
- **Usage:** Alternative to GitHub for hosting code
- **Components:**
  - Git repositories
  - Cloud Build integration
  - Access control
- **Note:** Not used if using GitHub

### 19. Container Analysis
- **API:** `containeranalysis.googleapis.com`
- **Purpose:** Container vulnerability scanning
- **Usage:** Scans Docker images in Artifact Registry for security issues
- **Components:**
  - Vulnerability scanning
  - Image metadata
  - Security reports
- **Note:** Auto-enabled with Artifact Registry

### 20. Container Scanning
- **API:** `containerscanning.googleapis.com`
- **Purpose:** Enhanced container security scanning
- **Usage:** Provides detailed security analysis of container images
- **Components:**
  - CVE detection
  - Compliance checking
  - Security recommendations
- **Note:** Part of Artifact Registry security features

---

## Billing & Admin Services

### 21. Cloud Billing
- **API:** `cloudbilling.googleapis.com`
- **Purpose:** Billing and cost management
- **Usage:** Tracks costs for all enabled services
- **Components:**
  - Billing accounts
  - Cost tracking
  - Budget alerts
  - Billing exports
- **Note:** Required for all paid services

### 22. Cloud Console
- **Web UI:** `console.cloud.google.com`
- **Purpose:** Web-based management interface
- **Usage:** Visual management of all GCP resources
- **Components:**
  - Dashboard
  - Resource browser
  - Cloud Shell
  - API explorer
- **Note:** Always available

---

## CLI & SDK Tools

### 23. gcloud CLI
- **Installation:** Google Cloud SDK
- **Purpose:** Command-line interface for GCP
- **Usage:** Execute all deployment commands
- **Components:**
  - gcloud commands
  - Authentication
  - Configuration management
  - Component management
- **Installation:** Manual download required

### 24. kubectl
- **Installation:** Via gcloud or standalone
- **Purpose:** Kubernetes cluster management
- **Usage:** Deploy and manage Kubernetes resources
- **Components:**
  - Deployment management
  - Pod inspection
  - Service configuration
  - Log viewing
- **Installation:** `gcloud components install kubectl`

### 25. Docker
- **Installation:** Docker Desktop or Docker Engine
- **Purpose:** Container building and testing
- **Usage:** Build Docker images locally and push to Artifact Registry
- **Components:**
  - Image building
  - Container runtime
  - Local testing
- **Installation:** Manual download required

### 26. apigeecli (Optional)
- **Installation:** Standalone CLI tool
- **Purpose:** Apigee management via command line
- **Usage:** Deploy and manage API proxies programmatically
- **Components:**
  - Proxy deployment
  - Environment management
  - API product management
- **Installation:** Manual download from GitHub

---

## Enable All Services

**Command:**
```bash
gcloud services enable \
  container.googleapis.com \
  artifactregistry.googleapis.com \
  cloudbuild.googleapis.com \
  apigee.googleapis.com
```

---

## Service Flow

```
External Request
    ↓
Apigee API Gateway (API management)
    ↓
GKE Cluster (Application hosting)
    ↓
Laravel App Container (from Artifact Registry)
```

**CI/CD Flow:**
```
Git Push → Cloud Build → Build Docker Image → Push to Artifact Registry → Deploy to GKE
```

---

## Additional GCP Components

These are used but don't require separate API enablement:

- **IAM (Identity and Access Management):** Service accounts and permissions
- **Cloud Console:** Web UI for managing resources
- **gcloud CLI:** Command-line tool for automation
- **kubectl:** Kubernetes management (configured after GKE setup)

---

## Cost Considerations

All enabled services may incur charges:
- **GKE:** Cluster nodes (compute instances)
- **Artifact Registry:** Storage for container images
- **Cloud Build:** Build minutes
- **Apigee:** API calls and provisioned instance

Monitor costs at: https://console.cloud.google.com/billing
