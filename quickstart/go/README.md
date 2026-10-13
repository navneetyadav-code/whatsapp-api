# 🐹 Go Quickstart — WhatsApp API Hub

A high-performance Go client with **zero external dependencies** (uses only standard library `net/http` and `encoding/json`).

## 🚀 Quick Usage

### 1. Interactive Mode
Run directly with `go run`:
```bash
go run main.go
```

### 2. Command Line Arguments
```bash
# Standard clean format (Recommended)
go run main.go -key wa_live_YOUR_KEY -to 919876543210 -message "Hello from Go!"

# Meta WhatsApp Cloud API format (Drop-In)
go run main.go -key wa_live_YOUR_KEY -to 919876543210 -message "Hello!" -format meta
```

### 3. Build a Binary Executable
```bash
# Build for your OS
go build -o send_whatsapp main.go

# Run the compiled binary
./send_whatsapp -to 919876543210 -message "Alert triggered!"
```

## ⚙️ Environment Variables
```bash
export WA_API_KEY="wa_live_YOUR_KEY"
export WA_API_URL="https://whatsapp-api-xepo.onrender.com/api/send.php"
```
