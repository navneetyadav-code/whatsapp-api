package main

import (
	"bytes"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"time"
)

type MessagePayload struct {
	To       string `json:"to"`
	Message  string `json:"message"`
	Humanize bool   `json:"humanize"`
}

func main() {
	apiURL := "http://localhost/whatsapp-api/public/api/send.php"
	apiKey := "wa_live_YOUR_API_KEY_HERE"

	payload := MessagePayload{
		To:       "919876543210",
		Message:  "Hello from Go! 🐹 Free WhatsApp API Gateway.",
		Humanize: true,
	}

	jsonData, err := json.Marshal(payload)
	if err != nil {
		fmt.Printf("JSON marshal error: %v\n", err)
		return
	}

	req, err := http.NewRequest("POST", apiURL, bytes.NewBuffer(jsonData))
	if err != nil {
		fmt.Printf("Request error: %v\n", err)
		return
	}

	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("X-API-Key", apiKey)

	client := &http.Client{Timeout: 10 * time.Second}
	resp, err := client.Do(req)
	if err != nil {
		fmt.Printf("HTTP error: %v\n", err)
		return
	}
	defer resp.Body.Close()

	body, _ := io.ReadAll(resp.Body)
	fmt.Printf("HTTP Status: %d\nResponse: %s\n", resp.StatusCode, string(body))
}
