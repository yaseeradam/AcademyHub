# SuperAdmin Dashboard — Feature Roadmap

## Status Legend
- [ ] Not started
- [~] In progress
- [x] Done

---

## 🏫 School Management

- [x] **Impersonate School Admin** — log in as any school's admin with one click (full audit trail, exit button)
- [x] **School Health Check** — badge showing if school has active session/term, students, teacher allocations
- [x] **Reset School Data** — wipe a tenant's data and start fresh (with confirmation + backup prompt)
- [ ] **Clone School** — copy settings/classes/subjects/fee structures from one tenant to another
- [ ] **School Activity Feed** — last login, last student added, last score entry, last exam created
- [x] **Maintenance Mode Per Tenant** — put one school offline without affecting others

---

## 💳 Payments & Subscriptions

- [x] **Subscription Manager** — set expiry date, plan, grace period per school
- [ ] **Payment History Log** — record of all payments received per school with date/amount/method
- [ ] **Invoice Generator** — generate PDF invoice and send to school contact email
- [ ] **Auto-Suspend** — automatically suspend schools that are past due date + grace period
- [ ] **Revenue Dashboard** — MRR, ARR, churn rate, upcoming renewals chart
- [ ] **Plan Limits Enforcement** — block student/teacher creation when school exceeds plan cap

---

## 📊 Analytics & Monitoring

- [x] **Cross-Tenant Stats** — total students, teachers, exams, homework across all schools
- [x] **Storage Usage Per School** — disk space used by uploads per tenant
- [x] **Error Monitor** — schools with recent 500 errors and stack traces
- [x] **WhatsApp Bot Activity** — messages sent/received per school, registration count
- [x] **Dormant Schools** — flag schools with no activity in 30+ days
- [ ] **Login Activity** — last login per school, daily active users per tenant

---

## 🔧 System Control

- [x] **Feature Flags Per School** — toggle CBT, WhatsApp, Parent Portal, AI features per tenant
- [x] **Broadcast Announcement** — send system-wide notice to all school admins
- [x] **Force Password Reset** — require all users of a specific school to reset passwords
- [x] **Backup Management** — trigger backup request for any school from superadmin
- [ ] **Global Settings Override** — set default CA/exam marks, currency, report card template for new schools

---

## 👥 User Management

- [x] **Global User Search** — find any user (admin/teacher/student) across all schools
- [x] **Impersonate Any User** — log in as any admin for support purposes
- [ ] **Suspicious Activity Flags** — accounts with too many failed logins or unusual patterns
- [ ] **Bulk Deactivate Users** — deactivate all users of a suspended school at once

---

## 📱 App & Integrations

- [ ] **Per-School API Keys** — issue and revoke API keys for mobile app access per tenant
- [ ] **Mobile App Version Control** — force update notice or block old app versions per school
- [ ] **Webhook Logs** — view all outgoing notifications per school
- [ ] **WhatsApp Bot Status** — show which schools have WhatsApp linked, verified, and subscribed

---

## 🚀 Implementation Order

1. [x] Impersonate School Admin
2. [x] Subscription Manager + Auto-Suspend
3. [x] Feature Flags Per School
4. [x] School Health Check
5. [x] Revenue Dashboard
6. [x] Broadcast Announcement
7. [x] Global User Search + Impersonate Any User
8. [x] Storage Usage + Backup Management
9. [x] Cross-Tenant Analytics
10. [x] Clone School
11. [ ] Error Monitor
12. [ ] WhatsApp Bot Activity
13. [ ] Per-School API Keys
14. [ ] Suspicious Activity Flags
