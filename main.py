import asyncio
import json
from playwright.async_api import async_playwright
import sys

# Get email and password from command-line arguments
email = sys.argv[1]
password = sys.argv[2]

# Load users from JSON file
with open('selected_users.json', 'r') as file:
    data = json.load(file)
    users = data['users']
    closing_time = data['closing_time']

async def send_message(person):
    try:
        print(f"Attempting to send message to: {person['name']}")
        async with async_playwright() as p:
            browser = await p.chromium.launch(headless=False)
            page = await browser.new_page()
            print("Navigating to LinkedIn login page")
            await page.goto("https://ro.linkedin.com")
            print("Filling login credentials")
            await page.fill("#session_key", email)
            await page.fill("#session_password", password)
            await page.click("button[data-id='sign-in-form__submit-btn']")
            print("Waiting for home page to load")
            await page.wait_for_selector("#global-nav")
            print("Navigating to connections page")
            await page.goto("https://www.linkedin.com/mynetwork/invite-connect/connections/")
            print("Waiting for connections list to load")
            await page.wait_for_selector(".scaffold-finite-scroll__content")
            await asyncio.sleep(2)
            
            print("Opening message box")
            message_button = await page.query_selector(f"button[aria-label='Trimiteți un mesaj către {person['name']}']")
            if message_button:
                await message_button.click()
                await asyncio.sleep(2)

                await page.fill(".msg-form__contenteditable", person['message'])
                await asyncio.sleep(2)

                submit_button = await page.query_selector(".msg-form__send-button")
                if submit_button:
                    await submit_button.click()
                    print(f"Message sent to {person['name']} successfully.")
                else:
                    print(f"Submit button not found for {person['name']}.")
            else:
                print(f"No message button found for {person['name']}.")
    except Exception as e:
        print(f"Error sending message to {person['name']}: {e}")

async def main():
    try:
        print(f"Loaded users: {users}")
        await asyncio.gather(*[send_message(person) for person in users])
    except Exception as e:
        print(f"An error occurred: {e}")

if __name__ == "__main__":
    asyncio.run(main())
