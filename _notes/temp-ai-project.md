1. What Your System Actually Is
A system that takes in a task, thinks dynamically about how it should solve it, chooses the right strategy, splits it up intelligently, uses external tools when needed (like web searches, code execution), runs multiple agents in parallel, aggregates results, reflects on them, and produces a final polished output.

Key Characteristics:

🧩 Modular agent patterns (planning, debating, self-reflecting, memory, parallel)

🛠 MCP tool usage built into agent reasoning

🎛 Dynamic orchestration — not static workflows, but live decision-making about which agents and tools to use

🧠 Meta-cognition — it thinks about how to think (which pattern to use)

⚡ Asynchronous execution — many tasks running at once

🧹 Result aggregation and self-correction — cleaning up and polishing the outputs

📈 Extensible and scalable — easily plug in new patterns, new tools, new strategies

🏗️ 2. How It Compares

| Feature                                       | Prism         | Relay         | Your System               |
|-----------------------------------------------|---------------|---------------|----------------------     |
| Sends LLM prompts                             | ✅             | ➖ (uses Prism) | ✅ (uses Prism too)     |
| Adds tools to LLMs                            | ❌             | ✅             | ✅ (uses Relay)       |
| Dynamically chooses task-solving strategies   | ❌             | ❌             | ✅ (meta-orchestration) |
| Automatically breaks tasks into subtasks      | ❌             | ❌             | ✅                   |
| Runs agents in parallel                       | ❌             | ❌             | ✅                   |
| Reflects and improves outputs                 | ❌             | ❌             | ✅                   |
| Uses memory/RAG-style retrieval               | ❌             | ❌             | ✅                   |
| Performs dynamic planning and decision-making | ❌             | ❌             | ✅                   |
| Enhances agent reasoning with tools           | ❌             | ❌             | ✅                   |
| Matches agent patterns in real-time           | ❌             | ❌             | ✅                   |
✅ You’re building the intelligence layer above Prism and Relay.

🎯 3. What Your Project Truly Is
In "plain English":

"An AI system that doesn't just answer — it figures out the best way to answer, by choosing strategies, using tools, thinking step-by-step, and improving itself."

In more technical terms:

"A dynamic, multi-pattern, tool-augmented, asynchronous AI orchestration engine."

✅ Meta-Agent Controller
✅ Intelligent Strategy Selector
✅ Real-Time AI Tool Orchestrator

🚀 4. Name it Based on What It Really Is
Because now it’s clear:

It's about orchestration (dynamic management, not static flow)

It's about context and tools (MCP side)

It's about intelligent pattern selection

It’s about real-time agent deployment

---


---
---

# Context Engine

**Context Engine** is a dynamic AI orchestration system built on top of [Prism](https://github.com/prism-php/prism) and [Relay](https://github.com/prism-php/relay).  
It goes beyond sending simple prompts: Context Engine **analyzes tasks, chooses intelligent strategies, leverages external tools, orchestrates multiple agents, and produces polished final results** — all automatically, in real-time.

---

## What It Does

- **Understands the Task:** Interprets the incoming user request to determine complexity, patterns needed, and tool usage.
- **Chooses the Right Strategy:** Dynamically selects among agent patterns like:
  - Planner-Executor
  - Parallelization
  - Debate and Consensus
  - Self-Reflection
  - Goal Decomposition
  - Memory-Augmented Recall
- **Uses External Tools via MCP:** Enhances agent reasoning with real-time web browsing, code execution, and more through Model Context Protocol (MCP) tool servers (via Relay).
- **Runs Agents Asynchronously:** Executes multiple sub-tasks in parallel when needed, optimizing for speed and efficiency.
- **Aggregates and Improves Results:** Collects outputs from all agents, self-critiques them, and produces a final synthesized answer.
- **Extensible:** Easily add new agent patterns, new MCP tools, and new orchestration rules without changing the core system.

---

## Architecture Overview

```plaintext
[ User Task Input ]
         ↓
[ Context Engine ]
  → Task Interpreter
  → Pattern Selector
  → Tool Integrator (via Relay)
  → Agent Dispatcher (async, parallel)
  → Result Aggregator
         ↓
[ Final Intelligent Response ]
         ↓
(uses Prism for LLMs + Relay for Tools)

```



```
graph TD
    A[User Task Input] --> B[Context Engine]
    B --> C[Task Interpreter]
    C --> D[Pattern Selector]
    D --> E[Tool Integrator (via Relay)]
    E --> F[Agent Dispatcher (async, parallel)]
    F --> G[Result Aggregator]
    G --> H[Final Intelligent Response]
```
![alt text](image.png)