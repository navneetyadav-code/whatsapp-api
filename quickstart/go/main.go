package main

import (
	"bufio"
	"bytes"
	"encoding/json"
	"flag"
	"fmt"
	"io"
	"net/http"
	"os"
	"strings"
	"time"
)

const defaultURL = "https://whatsapp-api-xepo.onrender.com/api/send.php"

// Client handles requests to the WhatsGateway
type Client struct {
	APIKey     string
	Endpoint   string
	HTTPClient *http.Client
}

// NewClient initializes a new WhatsApp API Client
func NewClient(apiKey, endpoint string) *Client {
	if endpoint == "" {
		endpoint = defaultURL
	}
	return &Client{
		APIKey:   apiKey,
		Endpoint: strings.TrimRight(endpoint, "/"),
		HTTPClient: &http.Client{
			Timeout: 20 * time.Second,
		},
	}
}

// SendStandard sends a message using the clean standard flat format
func (c *Client) SendStandard(to, message string, humanize bool) (map[string]interface{}, error) {
	payload := map[string]interface{}{
		"to":       strings.TrimSpace(to),
		"message":  strings.TrimSpace(message),
		"humanize": humanize,
	}

	headers := map[string]string{
		"Content-Type": "application/json",
		"X-API-Key":    c.APIKey,
	}

	return c.post(payload, headers)
}

// SendMetaFormat sends a message using Meta WhatsApp Cloud API structure
func (c *Client) SendMetaFormat(to, body string) (map[string]interface{}, error) {
	payload := map[string]interface{}{
		"messaging_product": "whatsapp",
		"recipient_type":    "individual",
		"to":                strings.TrimSpace(to),
		"type":              "text",
		"text": map[string]interface{}{
			"preview_url": false,
			"body":        strings.TrimSpace(body),
		},
	}

	headers := map[string]string{
		"Content-Type":  "application/json",
		"Authorization": "Bearer " + c.APIKey,
	}

	return c.post(payload, headers)
}

func (c *Client) post(payload interface{}, headers map[string]string) (map[string]interface{}, error) {
	bodyBytes, err := json.Marshal(payload)
	if err != nil {
		return nil, fmt.Errorf("failed to marshal JSON payload: %w", err)
	}

	req, err := http.NewRequest("POST", c.Endpoint, bytes.NewBuffer(bodyBytes))
	if err != nil {
		return nil, fmt.Errorf("failed to create HTTP request: %w", err)
	}

	for k, v := range headers {
		req.Header.Set(k, v)
	}

	resp, err := c.HTTPClient.Do(req)
	if err != nil {
		return nil, fmt.Errorf("HTTP request failed: %w", err)
	}
	defer resp.Body.Close()

	respBytes, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, fmt.Errorf("failed to read response body: %w", err)
	}

	var result map[string]interface{}
	if err := json.Unmarshal(respBytes, &result); err != nil {
		return map[string]interface{}{"raw": string(respBytes)}, nil
	}

	if resp.StatusCode < 200 || resp.StatusCode >= 300 {
		return result, fmt.Errorf("API responded with HTTP %d", resp.StatusCode)
	}

	return result, nil
}

func prompt(scanner *bufio.Scanner, label string) string {
	fmt.Print(label)
	if scanner.Scan() {
		return strings.TrimSpace(scanner.Text())
	}
	return ""
}

func main() {
	toFlag := flag.String("to", "", "Recipient phone number with country code")
	msgFlag := flag.String("message", "", "Message text")
	keyFlag := flag.String("key", os.Getenv("WA_API_KEY"), "API Key (wa_live_...)")
	urlFlag := flag.String("url", os.Getenv("WA_API_URL"), "API Endpoint URL")
	formatFlag := flag.String("format", "standard", "Format: standard or meta")
	noHumanizeFlag := flag.Bool("no-humanize", false, "Disable humanized delay")
	flag.Parse()

	scanner := bufio.NewScanner(os.Stdin)

	apiKey := *keyFlag
	if apiKey == "" {
		apiKey = prompt(scanner, "Enter your API Key (wa_live_...): ")
		if apiKey == "" {
			fmt.Fprintln(os.Stderr, "[!] API Key is required.")
			os.Exit(1)
		}
	}

	apiURL := *urlFlag
	if apiURL == "" {
		apiURL = defaultURL
	}

	recipient := *toFlag
	if recipient == "" {
		recipient = prompt(scanner, "Enter recipient phone number: ")
	}

	message := *msgFlag
	if message == "" {
		message = prompt(scanner, "Enter message text: ")
	}

	format := strings.ToLower(*formatFlag)

	fmt.Printf("\n[*] Sending message via [%s] format...\n", strings.ToUpper(format))
	fmt.Printf("[*] Endpoint:  %s\n", apiURL)
	fmt.Printf("[*] Recipient: %s\n", recipient)

	client := NewClient(apiKey, apiURL)

	var result map[string]interface{}
	var err error

	if format == "meta" {
		result, err = client.SendMetaFormat(recipient, message)
	} else {
		result, err = client.SendStandard(recipient, message, !*noHumanizeFlag)
	}

	prettyJSON, _ := json.MarshalIndent(result, "", "  ")

	if err != nil {
		fmt.Fprintf(os.Stderr, "\n[-] FAILED to send message: %v\n", err)
		if len(prettyJSON) > 0 {
			fmt.Fprintf(os.Stderr, "%s\n", string(prettyJSON))
		}
		os.Exit(1)
	}

	fmt.Printf("\n[+] SUCCESS! Message queued for delivery:\n%s\n", string(prettyJSON))
}
