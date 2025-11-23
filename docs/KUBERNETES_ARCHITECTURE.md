# Kubernetes Architecture Guide

This document explains Kubernetes concepts and clustering strategies for the Laravel + GKE deployment.

---

## Kubernetes Hierarchy

### Understanding the Structure (Top to Bottom):

```
Cluster (biggest - entire Kubernetes system)
  └─> Nodes (servers/VMs)
      └─> Pods (smallest deployable unit)
          └─> Containers (your application)
```

**Important:** Clusters contain Pods, NOT the other way around!

---

## 1. Cluster

**What is it:**
- The entire Kubernetes infrastructure
- Collection of servers (nodes) working together
- Manages all your applications

**Example:**
```bash
gcloud container clusters create laravel-cluster
                                 └─ Cluster name
```

**Analogy:** The entire factory building

---

## 2. Nodes

**What is it:**
- Individual servers (VMs) that run your containers
- Worker machines in the cluster

**Example configuration:**
```bash
--num-nodes=2           # Start with 2 VMs
--machine-type=e2-medium # Each VM size: 2 vCPU, 4GB RAM
--min-nodes=1           # Can scale down to 1 VM
--max-nodes=3           # Can scale up to 3 VMs
```

**Analogy:** Individual factory floors

---

## 3. Pods

**What is it:**
- Smallest deployable unit in Kubernetes
- Wraps one or more containers
- Usually 1 pod = 1 container (your Laravel app)

**Example:**
```bash
kubectl apply -f deployment.yaml
# Creates pods running your app1 container
```

**Analogy:** Individual workers on the factory floor

---

## Visual Hierarchy Example

```
┌─────────────────────────────────────────────┐
│ Cluster: laravel-cluster                   │
│                                             │
│  ┌───────────────────┐  ┌──────────────┐   │
│  │ Node 1            │  │ Node 2       │   │
│  │ (e2-medium VM)    │  │ (e2-medium)  │   │
│  │                   │  │              │   │
│  │  ┌──────┐ ┌────┐ │  │  ┌─────┐     │   │
│  │  │ Pod1 │ │Pod2│ │  │  │ Pod3│     │   │
│  │  │app1  │ │app1│ │  │  │ app1│     │   │
│  │  └──────┘ └────┘ │  │  └─────┘     │   │
│  └───────────────────┘  └──────────────┘   │
└─────────────────────────────────────────────┘
```

**Real example after deployment:**
- 1 Cluster: laravel-cluster
- 2 Nodes: node-1 and node-2 (VMs)
- 3 Pods: Running app1 containers (distributed across nodes)

---

## Clustering Strategies

### Strategy 1: One Cluster for Everything (Recommended for Small-Medium)

```
Cluster: laravel-cluster
  └─> Nodes: 2-3 VMs
      ├─> Pods: app1 (Laravel)
      ├─> Pods: app2 (Laravel)
      ├─> Pods: app3 (Node.js)
      ├─> Pods: app4 (Python)
      └─> Pods: redis, mysql, etc.
```

**Benefits:**
- Cost-efficient (shared nodes)
- Easier to manage (one cluster)
- Better resource utilization
- Simpler networking (apps can talk easily)

**Use when:**
- Same team manages all apps
- Apps belong to same project/company
- Cost is a concern
- < 50 microservices

---

### Strategy 2: One Cluster per Environment

```
Cluster: dev-cluster
  └─> app1-dev, app2-dev, app3-dev

Cluster: staging-cluster
  └─> app1-staging, app2-staging, app3-staging

Cluster: production-cluster
  └─> app1-prod, app2-prod, app3-prod
```

**Benefits:**
- Environment isolation
- Can test cluster changes in dev first
- Production incidents don't affect dev
- Different scaling per environment

**Use when:**
- Need strong environment separation
- Different teams manage different environments
- Compliance requires isolation

---

### Strategy 3: One Cluster per Business Unit/Team

```
Cluster: ecommerce-cluster
  └─> shopping-cart, payments, inventory

Cluster: analytics-cluster
  └─> data-pipeline, reporting, ml-models

Cluster: mobile-backend-cluster
  └─> user-api, push-notifications, auth
```

**Benefits:**
- Team autonomy (each team owns their cluster)
- Billing separation (track costs per team)
- Different security policies per team
- Blast radius limited (one team's issue doesn't affect others)

**Use when:**
- Large organization with multiple teams
- Different compliance requirements per team
- Need cost tracking per business unit

---

### Strategy 4: One Cluster per Critical App (Rare)

```
Cluster: payment-cluster (critical)
  └─> payment-service only

Cluster: everything-else-cluster
  └─> All other apps
```

**Benefits:**
- Ultra-critical apps isolated
- Dedicated resources guaranteed
- Maximum security for sensitive apps

**Use when:**
- App handles money/sensitive data
- Compliance requires physical isolation
- App has extreme performance needs

---

## Framework/Language Independence

**You DON'T need separate clusters for different stacks:**

### Good Practice:
```
✅ One Cluster
  ├─> app1 (PHP Laravel)
  ├─> app2 (Node.js)
  ├─> app3 (Python Django)
  └─> app4 (Go)
```

### Bad Practice:
```
❌ Separate Clusters
  ├─> php-cluster (just Laravel apps)
  ├─> nodejs-cluster (just Node apps)
  └─> python-cluster (just Python apps)
```

**Why:** Kubernetes handles different containers equally - the framework inside doesn't matter!

---

## Cost Comparison

### One Cluster (Shared Nodes):
```
Cluster: 3 nodes @ $30/month each = $90/month
  └─> Runs: app1, app2, app3, app4
```

### Multiple Clusters:
```
Cluster 1: 2 nodes @ $30/month = $60/month (app1)
Cluster 2: 2 nodes @ $30/month = $60/month (app2)
Cluster 3: 2 nodes @ $30/month = $60/month (app3)
Cluster 4: 2 nodes @ $30/month = $60/month (app4)
Total: $240/month
```

**Result:** 4x more expensive for the same workload!

---

## When to Create Multiple Clusters

### ✅ Create Separate Clusters For:
- Different environments (dev, staging, prod)
- Different teams with different needs
- Different compliance/security requirements
- Different geographic regions (US cluster, EU cluster)
- Business-critical apps needing isolation

### ❌ DON'T Create Separate Clusters For:
- Each business app (wasteful)
- Different programming languages (unnecessary)
- Different frameworks (doesn't matter)
- Organizational preference without technical reason

---

## Using Namespaces for Separation

**Better alternative to multiple clusters:**

```
One Cluster: laravel-cluster
  ├─> Namespace: app1
  │   └─> app1 pods
  ├─> Namespace: app2
  │   └─> app2 pods
  ├─> Namespace: app3
  │   └─> app3 pods
  └─> Namespace: shared
      └─> redis, mysql pods
```

**Benefits:**
- Logical separation (like folders)
- Share nodes (cost-efficient)
- Separate permissions per namespace
- Easier than managing multiple clusters

**Create namespace:**
```bash
kubectl create namespace app1
kubectl create namespace app2
```

**Deploy to namespace:**
```bash
kubectl apply -f deployment.yaml -n app1
```

---

## Real-World Examples

### Startup/Small Company:
```
1 Cluster
  └─> All apps (5-20 microservices)
```

### Medium Company:
```
3 Clusters
  ├─> dev-cluster (all dev apps)
  ├─> staging-cluster (all staging apps)
  └─> prod-cluster (all prod apps)
```

### Large Company (Google, Netflix):
```
100+ Clusters
  ├─> Per region (us-east, us-west, eu)
  ├─> Per team (payments, video, search)
  └─> Per environment (dev, prod)
```

---

## Recommended Strategy for This Project

### Current Setup: One Cluster for All Laravel Apps

```
Cluster: laravel-cluster
  └─> Nodes: 2-3 VMs (auto-scaling)
      ├─> app1 pods (Laravel)
      ├─> app2 pods (Laravel)
      ├─> app3 pods (Laravel)
      └─> Shared resources (if needed)
```

**Why this approach:**
- All apps are Laravel (similar resource needs)
- Managed by same team
- Cost-efficient (shared node resources)
- Easier to manage (one cluster to monitor)
- Use namespaces for logical separation

### Future Growth Options:

**When to add more clusters:**
```
1. Separate Production from Development
   ├─> dev-cluster (development/testing)
   └─> prod-cluster (production workloads)

2. Geographic Expansion
   ├─> us-cluster (US users)
   └─> eu-cluster (EU data residency)

3. Critical App Isolation
   ├─> payment-cluster (mission-critical)
   └─> general-cluster (other apps)
```

---

## Summary

**Cluster Hierarchy:**
- Cluster > Nodes > Pods > Containers

**Clustering Strategy:**
- ✅ One cluster for everything (best for small-medium projects)
- ✅ One cluster per environment (dev/staging/prod)
- ✅ Use namespaces for logical separation within cluster
- ❌ NOT one cluster per business app (too expensive)
- ❌ NOT one cluster per framework (unnecessary)

**This Project:**
- One `laravel-cluster` running all Laravel apps
- Use namespaces to separate apps logically
- Scale with more nodes, not more clusters
- Add clusters only when technically necessary (environments, regions, compliance)

---

## Quick Reference

### Create Cluster:
```bash
gcloud container clusters create laravel-cluster \
  --zone=us-central1-a \
  --num-nodes=2 \
  --machine-type=e2-medium \
  --enable-autoscaling \
  --min-nodes=1 \
  --max-nodes=3
```

### Create Namespace:
```bash
kubectl create namespace app1
```

### Deploy to Namespace:
```bash
kubectl apply -f deployment.yaml -n app1
```

### View Resources:
```bash
kubectl get nodes                    # View nodes
kubectl get pods                     # View pods (default namespace)
kubectl get pods -n app1            # View pods in specific namespace
kubectl get all --all-namespaces    # View everything
```
