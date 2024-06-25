import sys
import json
import asyncio
from playwright.async_api import async_playwright

async def fetch_connections(email, password):
    try:
        async with async_playwright() as p:
            browser = await p.chromium.launch(headless=False)
            page = await browser.new_page()
            await page.goto("https://www.linkedin.com/login")
            await page.fill("#username", email)
            await page.fill("#password", password)
            await page.click("button[data-litms-control-urn='login-submit']")
            await page.wait_for_load_state('load')
            await page.goto("https://www.linkedin.com/mynetwork/invite-connect/connections/")
            await page.wait_for_selector(".scaffold-finite-scroll__content")
            connections = await page.query_selector_all("button[aria-label*='Trimiteți un mesaj către']")
            connections_data = [{"name": (await connection.get_attribute('aria-label')).replace('Trimiteți un mesaj către ', '')} for connection in connections]
            print(json.dumps(connections_data))
    except Exception as e:
        print(f"Error: {e}")

if __name__ == "__main__":
    email = sys.argv[1]
    password = sys.argv[2]
    asyncio.run(fetch_connections(email, password))
