# ☕ Java Quickstart — WhatsApp API Hub

A modern Java client compatible with Java 11, 17, and 21+ using standard `java.net.http.HttpClient` with **zero external dependencies**.

## 🚀 Quick Usage

### 1. Run Directly (Java 11+)
You can execute the single-file source code directly without manual compilation:
```bash
java SendWhatsApp.java
```

### 2. Pass Arguments via CLI
```bash
java SendWhatsApp.java 919876543210 "Hello from Java Enterprise!"
```

### 3. Compile to `.class`
```bash
# Compile
javac SendWhatsApp.java

# Run
java SendWhatsApp 919876543210 "Transaction confirmed!"
```

## ⚙️ Environment Variables
```bash
export WA_API_KEY="wa_live_YOUR_KEY"
export WA_API_URL="https://whatsapp-api-xepo.onrender.com/api/send.php"
```
