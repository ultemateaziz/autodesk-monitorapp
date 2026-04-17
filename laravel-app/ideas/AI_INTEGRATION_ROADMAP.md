# AI Integration Roadmap — ArchEng Pro Monitor
**Created:** 26 March 2026
**Project:** Autodesk Activity Monitor (Laravel + Node.js)
**Purpose:** Future AI enhancement plan using open-source models — real use cases only, no marketing fluff

---

## Table of Contents

1. [Data Foundation — What We Already Have](#1-data-foundation)
2. [Use Case 1 — Anomaly Detection Engine](#2-use-case-1--anomaly-detection-engine)
3. [Use Case 2 — License Churn Prediction](#3-use-case-2--license-churn-prediction)
4. [Use Case 3 — AI Insight Narrative Generator](#4-use-case-3--ai-insight-narrative-generator)
5. [Use Case 4 — Natural Language Dashboard Query](#5-use-case-4--natural-language-dashboard-query)
6. [Use Case 5 — AI User Productivity Profile Score](#6-use-case-5--ai-user-productivity-profile-score)
7. [Recommended Architecture](#7-recommended-architecture)
8. [Implementation Priority Matrix](#8-implementation-priority-matrix)
9. [Quickest Win — Start Here](#9-quickest-win--start-here)
10. [Open-Source Stack Summary](#10-open-source-stack-summary)

---

## 1. Data Foundation

Everything AI in this project runs on data already being collected. No new tracking needed.

| Data Source | Table | Volume | AI Value |
|-------------|-------|--------|----------|
| Activity heartbeats (every 3 sec) | `activity_logs` | Millions of rows | Pattern recognition, anomaly detection |
| User × App × Time × Machine | `activity_logs` | Full matrix | Behaviour modelling |
| License assigned vs used | `user_licenses` | Gap data | Predictive waste detection |
| Department benchmarks | Aggregated from logs | Comparative | Clustering, recommendations |
| Ghost machines / inactive users | `activity_logs` | Inactivity flags | Churn prediction |
| 7-day + 30-day usage trends | Aggregated from logs | Time series | Forecasting |
| User profiles + departments | `user_profiles` | Metadata | Context enrichment |
| Revoked software records | `revoked_software` | Audit trail | Pattern of problems |

### Key Technical Facts
- Each row in `activity_logs` = **3 seconds** of activity (heartbeat)
- 1200 heartbeats = 1 hour of usage
- Fields: `machine_name`, `user_name`, `application` (raw process name), `recorded_at`
- Application names are normalised via `mapApplicationName()` in `DashboardController`

---

## 2. Use Case 1 — Anomaly Detection Engine

### What It Does
Learns each user's **normal usage pattern**, then automatically flags when something unusual happens — without any manual rules.

### Real-World Examples from This Data
| Scenario | Why It's an Anomaly |
|----------|-------------------|
| Ahmed always works 8am–5pm. Today shows activity at 2am | Outside normal time window |
| Sara's weekly hours drop from 35h → 4h in one week | Sudden disengagement spike |
| A machine logs `acad.exe` for a user who never used AutoCAD | Unknown application for user |
| User's primary app switches overnight from Revit to 3ds Max | Abnormal app change |
| Machine logs activity but assigned user is on leave | Potential unauthorised access |

### How It Works
```
ActivityLog data (last 90 days per user)
        ↓
Python service builds baseline profile per user
        ↓
scikit-learn Isolation Forest model
        ↓
Anomaly score calculated per new activity batch
        ↓
Score > threshold → write alert to notifications table
        ↓
Bell icon in dashboard shows the alert
```

### Where It Shows in the App
- **Notification bell** (already exists) — new data source, no new UI needed
- Example alert: `⚠️ Unusual activity — John logged AutoCAD at 02:14am (not typical for this user)`

### Open-Source Stack
| Tool | Purpose |
|------|---------|
| `scikit-learn` — Isolation Forest | Anomaly detection model |
| `pandas` | Data aggregation from MySQL |
| `Python FastAPI` | Microservice called by Laravel |
| Laravel `Http::post()` | Calls Python service |

### Files to Create
```
laravel-app/
├── app/Services/AnomalyService.php       ← Laravel calls this
├── app/Console/Commands/RunAnomalyCheck.php  ← Scheduled command

ai-service/                                ← New Python project
├── main.py                                ← FastAPI app
├── models/anomaly_detector.py             ← Isolation Forest logic
├── requirements.txt
```

### Schedule
```php
// routes/console.php
Schedule::command('ai:anomaly-check')->hourly();
```

---

## 3. Use Case 2 — License Churn Prediction

### What It Does
Predicts which **currently active** licensed users will **stop using their software** within the next 30 days — **before** they become a ghost user.

### Current State vs AI-Enhanced State
| Current App | With AI |
|-------------|---------|
| Shows users who **already** went ghost (reactive) | Predicts users **about to** go ghost (proactive) |
| Static inactivity flags | Confidence score + timeline |
| No trend projection | 4-week forecast per user |

### Real Output on License Optimization Page
```
┌──────────────────────────────────────────────────────────┐
│  ⚠️  AT RISK — Likely to stop using within 30 days       │
├──────────────────────────────────────────────────────────┤
│  Khalid       AutoCAD     78% churn probability          │
│  Usage:  40h → 28h → 11h → 3h  (last 4 weeks declining) │
│  Action: Reassign license or confirm project status       │
├──────────────────────────────────────────────────────────┤
│  Fatima       Revit        61% churn probability          │
│  Usage:  22h → 18h → 9h → 6h   (gradual decline)        │
│  Action: Check if project phase changed                   │
└──────────────────────────────────────────────────────────┘
```

### How It Works
```
Weekly hours per user per app (last 12 weeks)
        ↓
Meta Prophet time-series model
        ↓
Forecasts next 4 weeks of usage
        ↓
If forecast < 2h/week → label as "at risk"
        ↓
Confidence score + estimated weeks until ghost
        ↓
Returns to Laravel → shown on License Optimization page
```

### Business Value
- Average UAE AutoCAD license: **AED 8,000/year**
- Autodesk Architecture Collection: **AED 25,500/year**
- 10 early-recovered licenses = **AED 80,000–255,000 saved per year**
- Payback on AI implementation: **immediate**

### Open-Source Stack
| Tool | Purpose |
|------|---------|
| `Prophet` by Meta | Time-series forecasting (pip install prophet) |
| `pandas` + `SQLAlchemy` | Pull weekly aggregates from MySQL |
| `Python FastAPI` | Microservice |
| No GPU required | Runs on CPU, Windows Server compatible |

### Files to Create
```
ai-service/
├── models/churn_predictor.py     ← Prophet forecasting logic
├── routes/churn.py               ← FastAPI endpoint /predict/churn

laravel-app/
├── app/Services/ChurnPredictionService.php
├── app/Console/Commands/RunChurnPrediction.php
```

### Schedule
```php
// routes/console.php
Schedule::command('ai:churn-predict')->weekly();
```

---

## 4. Use Case 3 — AI Insight Narrative Generator

### What It Does
Converts raw numbers into **readable English paragraphs** for HR email reports and the dashboard — using a local LLM running on the server. No API keys. No cost per call.

### Current Weekly Email (Numbers Only)
```
Ahmed:   32h | Top app: AutoCAD | Days active: 5 | Rating: High
Sara:    12h | Top app: Revit   | Days active: 3 | Rating: Low
Khalid:  28h | Top app: Revit   | Days active: 5 | Rating: Medium
```

### With AI Narrative (Generated Paragraph)
```
"This week the Architecture department logged 187 total hours, a 12%
increase from last week. Ahmed led the team with 32 hours, maintaining
a consistent 6.4h daily output. Sara showed a concerning drop from her
usual 28h average — only 12h recorded this week across 3 active days.
It may be worth a check-in conversation. The department's primary tool
remains AutoCAD at 64% of total usage time, with Revit adoption growing
steadily at 28%. Overall, team engagement is trending positively."
```

### How It Works
```
Weekly stats collected by SendWeeklyReport command
        ↓
Stats converted to JSON payload
        ↓
Laravel sends to Ollama API (localhost:11434)
        ↓
Mistral 7B generates paragraph
        ↓
Paragraph injected into email template
        ↓
HR receives email with numbers + AI narrative
```

### Laravel Integration (Simple)
```php
// In SendWeeklyReport.php — add this before sending email
$statsJson = json_encode([
    'department'       => $department,
    'week'             => $weekLabel,
    'total_hours'      => $totalTeamHours,
    'member_count'     => $userStats->count(),
    'top_performer'    => $userStats->sortByDesc('hours')->first(),
    'members'          => $userStats->toArray(),
]);

$response = Http::post('http://localhost:11434/api/generate', [
    'model'  => 'mistral',
    'prompt' => "You are an HR analytics assistant. Write a 3-4 sentence
                 professional summary paragraph for this weekly team
                 performance data. Be specific, factual, and highlight
                 any concerns: {$statsJson}",
    'stream' => false,
]);

$narrative = $response->json('response');
// Pass $narrative to email template
```

### Ollama Installation on Windows Server
```powershell
# Step 1 — Download installer from https://ollama.com/download/windows
# Step 2 — Install (no GPU required)
# Step 3 — Pull model
ollama pull mistral
# OR for smaller/faster model:
ollama pull phi3

# Step 4 — Verify
curl http://localhost:11434/api/tags
```

### Open-Source Stack
| Tool | Purpose |
|------|---------|
| **Ollama** | Local LLM runtime (free, Windows native) |
| **Mistral 7B** | Primary model (4GB RAM, CPU-only) |
| **Phi-3 Mini** | Lighter alternative (2GB RAM) |
| **LLaMA 3.2** | Best quality option (8GB RAM) |

### Where It Shows in the App
- Weekly HR email reports → additional paragraph after table
- Individual user reports → personal paragraph
- Dashboard → "This Week's Insight" card (optional)

---

## 5. Use Case 4 — Natural Language Dashboard Query

### What It Does
Admin types a question in plain English. AI converts it to a database query and returns the answer — no SQL knowledge needed.

### Real Query Examples
```
"Who worked the most hours last week in MEP?"
→ "Mohammed — 41h 20m, primarily using Revit and Navisworks"

"Which users haven't used their assigned license in 30 days?"
→ Table: 4 users with their assigned software and last-seen dates

"How does Architecture compare to Structural this month?"
→ Side-by-side: Architecture 324h avg 6.5h/day | Structural 198h avg 4.2h/day

"Who is using AutoCAD 2026 specifically?"
→ List of 7 users with individual hours logged

"Which machine hasn't been used in 2 weeks?"
→ Ghost machine list filtered to 14+ days
```

### How It Works
```
User types question in dashboard search bar
        ↓
Laravel sends question + DB schema to Ollama
        ↓
Ollama returns safe READ-ONLY SQL query
        ↓
Laravel validates query (SELECT only, no DROP/DELETE)
        ↓
Runs query on DB → formats results
        ↓
Returns plain-English answer + data table
```

### DB Schema Context for Prompt
```php
$schema = "
Tables available:
- activity_logs: id, machine_name, user_name, application, recorded_at
- user_profiles: user_name, display_name, department, email
- user_licenses: user_name, software_name, assigned_date
- revoked_software: user_name, software_name, type (suspended/permanent)

Rules: Only SELECT queries. application field contains raw names like
'acad.exe 2026', 'revit.exe'. Use LIKE for app matching.
recorded_at is UTC timestamp.
";
```

### Open-Source Stack
| Tool | Purpose |
|------|---------|
| **Ollama + SQLCoder** | Purpose-built text-to-SQL model |
| **Mistral 7B** | Alternative (less specialised but flexible) |
| Laravel query validator | Whitelist SELECT-only queries |

### Where It Shows
- Existing search bar at top of dashboard — repurposed
- Or new dedicated "Ask AI" panel (floating button)

---

## 6. Use Case 5 — AI User Productivity Profile Score

### What It Does
Replaces the basic "hours vs 160h target" score with a **multi-dimensional AI assessment** of each user, including consistency, tool diversity, peak performance hours, engagement trend, and risk signals.

### Current Profile Page
```
Productivity Score: 78%
(Based on 125h / 160h monthly target)
```

### With AI Assessment Card
```
┌─────────────────────────────────────────────────────┐
│  🤖  AI Productivity Assessment                      │
│                                                     │
│  Consistency Score:    ████████░░  82%              │
│  Tool Proficiency:     ███████░░░  74%              │
│  Engagement Trend:     ↑ +18% this month            │
│  Peak Work Window:     8am – 11am (most productive) │
│  Risk Signal:          None detected ✅              │
│                                                     │
│  "Ahmed maintains strong daily habits with a peak   │
│   performance window in the morning. His Revit      │
│   usage has increased 23% — possible upskilling     │
│   in BIM workflows. Compared to department average, │
│   he performs 15% above benchmark."                 │
│                                                     │
│  Last updated: Today at 08:00                       │
└─────────────────────────────────────────────────────┘
```

### How It Works
```
Profile page loads → check cache (24h TTL)
        ↓ (cache miss)
Fetch last 30 days activity for this user
        ↓
Calculate 5 metrics:
  1. Consistency: std deviation of daily hours (lower = more consistent)
  2. Tool Diversity: count of unique apps used regularly
  3. Trend: current month vs last month hours delta
  4. Peak Hours: hour bucket with highest activity count
  5. Risk: any anomaly flags from Use Case 1
        ↓
Send metrics JSON to Ollama
        ↓
Ollama writes plain-English paragraph
        ↓
Cache result for 24 hours
        ↓
Displayed on profile page
```

### Caching Strategy (Important)
```php
// In ProfileController.php
$cacheKey = "ai_profile_{$userName}";
$aiInsight = Cache::remember($cacheKey, now()->addHours(24), function() use ($userName, $metrics) {
    return $this->generateAIInsight($metrics);
});
```
> **Why cache?** Ollama generates text in 1–3 seconds. Without cache, every profile page load would call the LLM. Cache once per day per user = negligible server load.

### Open-Source Stack
| Tool | Purpose |
|------|---------|
| Ollama + Mistral | Paragraph generation |
| Laravel Cache | 24h result caching |
| PHP calculation | 5 metrics computation (no Python needed) |

---

## 7. Recommended Architecture

```
┌──────────────────────────────────────────────────────────────┐
│                    Windows Server                             │
│                                                              │
│   ┌────────────────────┐      ┌──────────────────────────┐   │
│   │   Laravel App       │      │   Python AI Service       │   │
│   │   (port 8001)      │◄────►│   (port 8100)             │   │
│   │                    │      │   FastAPI                 │   │
│   │   Sends data JSON  │      │   - Anomaly Detection     │   │
│   │   Gets results     │      │   - Churn Prediction      │   │
│   │   Caches 24h       │      │   - Isolation Forest      │   │
│   └────────────────────┘      │   - Prophet Forecasting   │   │
│            │                  └──────────────────────────┘   │
│            │                                                  │
│            ▼                                                  │
│   ┌────────────────────┐                                      │
│   │   Ollama Service    │                                      │
│   │   (port 11434)     │                                      │
│   │   CPU-only mode    │                                      │
│   │   - Mistral 7B     │                                      │
│   │   - Phi-3 Mini     │                                      │
│   │                    │                                      │
│   │   Used for:        │                                      │
│   │   - Narratives     │                                      │
│   │   - NL queries     │                                      │
│   │   - Profile text   │                                      │
│   └────────────────────┘                                      │
│                                                              │
│   ┌────────────────────┐                                      │
│   │   MySQL Database    │                                      │
│   │   (port 3306)      │                                      │
│   │   - activity_logs  │                                      │
│   │   - user_profiles  │                                      │
│   └────────────────────┘                                      │
└──────────────────────────────────────────────────────────────┘
```

### Service Communication
```
Laravel → Ollama:        Http::post('http://localhost:11434/api/generate')
Laravel → Python AI:     Http::post('http://localhost:8100/api/anomaly')
Laravel → Python AI:     Http::post('http://localhost:8100/api/churn')
Python AI → MySQL:       Direct connection via SQLAlchemy (read-only user)
```

---

## 8. Implementation Priority Matrix

| Priority | Use Case | Dev Effort | Server Load | Business Impact | Start With |
|----------|----------|-----------|-------------|-----------------|------------|
| 🔴 **P1** | AI Narrative in Email Reports | **Low** (1–2 days) | Low (weekly only) | **High** — HR loves it | ✅ Yes |
| 🔴 **P1** | User Profile AI Score | **Low** (1 day) | Low (cached 24h) | **High** — visible daily | ✅ Yes |
| 🟡 **P2** | License Churn Prediction | Medium (3–5 days) | Low (weekly batch) | **Very High** — saves AED | After P1 |
| 🟡 **P2** | Anomaly Detection | Medium (3–5 days) | Medium (hourly) | Medium — security value | After P1 |
| 🟢 **P3** | NL Dashboard Query | High (1–2 weeks) | Medium (on demand) | Medium — nice to have | Later |

---

## 9. Quickest Win — Start Here

### Step 1 — Install Ollama on Windows Server (5 minutes)
```powershell
# Download from: https://ollama.com/download/windows
# After install — pull a model:
ollama pull mistral

# Verify it works:
curl http://localhost:11434/api/tags
```

### Step 2 — Test the API from Laravel (5 minutes)
```php
// Paste this in tinker: php artisan tinker
$response = \Illuminate\Support\Facades\Http::post('http://localhost:11434/api/generate', [
    'model'  => 'mistral',
    'prompt' => 'Write a 2-sentence productivity summary: Team worked 187 hours this week, up 12% from last week. Top performer was Ahmed with 32 hours using AutoCAD.',
    'stream' => false,
]);
echo $response->json('response');
```

### Step 3 — Add to Weekly Email (1 day of work)
- Open `app/Console/Commands/SendWeeklyReport.php`
- Before `Mail::send(...)`, call Ollama with the `$userStats` data
- Pass the returned `$narrative` string into `WeeklyTeamReport` mailable
- Add `{{ $narrative }}` to `resources/views/emails/weekly_report.blade.php`

**Result:** Every Monday, HR receives AI-written insight paragraphs alongside the data table. Zero extra infrastructure cost. No API keys. No GPU. ✅

---

## 10. Open-Source Stack Summary

| Tool | Type | Use In This Project | Installation |
|------|------|--------------------|----|
| **Ollama** | Local LLM runtime | Narrative generation, NL queries, profile text | `winget install Ollama.Ollama` |
| **Mistral 7B** | LLM model (4GB) | Best balance of quality vs speed | `ollama pull mistral` |
| **Phi-3 Mini** | LLM model (2GB) | Faster, lower RAM, good for summaries | `ollama pull phi3` |
| **LLaMA 3.2** | LLM model (8GB) | Best quality, needs more RAM | `ollama pull llama3.2` |
| **Prophet** | Time-series forecasting | License churn prediction | `pip install prophet` |
| **scikit-learn** | ML library | Anomaly detection (Isolation Forest) | `pip install scikit-learn` |
| **FastAPI** | Python web framework | AI microservice | `pip install fastapi uvicorn` |
| **pandas** | Data manipulation | Aggregating activity logs for models | `pip install pandas` |
| **SQLAlchemy** | Python ORM | Python service reads MySQL | `pip install sqlalchemy pymysql` |

### Minimum Server Requirements for AI Features
| Feature | Extra RAM | Extra CPU | GPU? |
|---------|-----------|-----------|------|
| Ollama + Mistral 7B | +4 GB | Low (inference only) | ❌ Not needed |
| Prophet (churn) | +512 MB | Low (weekly batch) | ❌ Not needed |
| Isolation Forest | +256 MB | Low (hourly batch) | ❌ Not needed |
| **Total additional** | **~5 GB RAM** | **Minimal** | **None** |

---

## Notes for Future Development

1. **Always cache AI responses** — LLM calls take 1–3 seconds. Cache profile scores for 24h, department insights for 1h, churn predictions for 7 days.

2. **Never call AI on every page load** — Use scheduled commands (`php artisan ai:*`) to pre-generate and store AI results in the database or cache.

3. **Keep AI read-only** — AI services should never write directly to the database. Laravel handles all writes. Python/Ollama only read and return analysis.

4. **Fallback gracefully** — If Ollama is down, show the regular numbers without the narrative. Wrap all AI calls in try/catch with fallback to empty string.

5. **Start with Ollama narratives** — Lowest risk, highest visible impact, zero extra cost. Get management buy-in, then expand to anomaly detection and prediction.

6. **Model selection guidance:**
   - Fast server / limited RAM → use `phi3` (2GB)
   - Normal server → use `mistral` (4GB)
   - High-end server → use `llama3.2` (8GB) for best quality

---

*Document generated: 26 March 2026*
*Project: ArchEng Pro Monitor — AI Roadmap v1.0*
