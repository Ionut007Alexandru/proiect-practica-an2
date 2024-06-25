import asyncio
import json
from playwright.async_api import async_playwright

linkedin_email_or_phone = "hiriscauionutalexandru@gmail.com"
linkedin_password = "parola123"

lock = asyncio.Lock()

async def send_message(person):
    try:
        async with lock: 
            print(f"Attempting to send message to: {person['name']}")
            async with async_playwright() as p:
                browser = await p.chromium.launch(headless=False)
                page = await browser.new_page()
                await page.goto("https://ro.linkedin.com")
                await page.fill("#session_key", linkedin_email_or_phone)
                await page.fill("#session_password", linkedin_password)
                await page.click("button[data-id='sign-in-form__submit-btn']")
                await page.goto("https://www.linkedin.com/mynetwork/invite-connect/connections/")
                await asyncio.sleep(2)

                while True:
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
                        await page.goto("https://www.linkedin.com/mynetwork/invite-connect/connections/")
                        await asyncio.sleep(2)
                        break
                    else:
                        print(f"No message button found for {person['name']}.")
                        break
    except Exception as e:
        print(f"Error sending message to {person['name']}: {e}")

async def main():
    try:
        with open('selected_users.json', 'r') as file:
            persons = json.load(file)
        print(f"Loaded users: {persons}")
        await asyncio.gather(*[send_message(person) for person in persons])
    except Exception as e:
        print(f"An error occurred: {e}")

if __name__ == "__main__":
    asyncio.run(main())
