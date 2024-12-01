from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select
import time
import os

# Specify the path to your ChromeDriver
driver = webdriver.Chrome()

# URLs for signup, login, upload, and add revenue pages
signup_url = "http://localhost/itms/signup.php"
login_url = "http://localhost/itms/login.php"
upload_url = "http://localhost/itms/upload_file.php"
add_revenue_url = "http://localhost/itms/add_revenue.php"

# Test Data
test_user = {
    "name": "testuser",
    "address": "123 Test Lane",
    "email": "testuser7@example.com",
    "phone": "1234567890",
    "password": "TestPass123",
    "role": "taxpayer"
}

# Set a path to a sample PDF file for upload testing
sample_pdf_path = "C:/xampp/htdocs/itms/uploads/ml18.pdf"  # Update this to the path of an actual PDF file

def test_signup():
    """Function to test the signup process."""
    driver.get(signup_url)
    time.sleep(2)

    driver.find_element(By.NAME, "name").send_keys(test_user["name"])
    driver.find_element(By.NAME, "address").send_keys(test_user["address"])
    driver.find_element(By.NAME, "email").send_keys(test_user["email"])
    driver.find_element(By.NAME, "phone").send_keys(test_user["phone"])
    driver.find_element(By.NAME, "password").send_keys(test_user["password"])

    # Select the role
    role_select = Select(driver.find_element(By.NAME, "role"))
    role_select.select_by_value(test_user["role"])

    # Submit the form
    driver.find_element(By.TAG_NAME, "button").click()
    time.sleep(3)

    # Check if redirected to login page
    assert "login.php" in driver.current_url, "Signup failed - did not redirect to login page."
    print("Signup test passed - redirected to login page.")

def test_login():
    """Function to test the login process."""
    driver.get(login_url)
    time.sleep(2)

    driver.find_element(By.NAME, "name").send_keys(test_user["name"])
    driver.find_element(By.NAME, "password").send_keys(test_user["password"])

    # Submit the login form
    driver.find_element(By.TAG_NAME, "button").click()
    time.sleep(1)

    # Check if redirected to taxpayer page
    assert "taxpayer.php" in driver.current_url, "Login failed - did not redirect to taxpayer page."
    print("Login test passed - redirected to taxpayer dashboard.")

def test_upload_file():
    """Function to test file upload functionality."""
    driver.get(upload_url)
    time.sleep(2)

    # Select the file input and upload a PDF file
    file_input = driver.find_element(By.NAME, "document")
    file_input.send_keys(sample_pdf_path)

    # Submit the upload form
    driver.find_element(By.TAG_NAME, "button").click()
    time.sleep(3)

    # Verify upload success message
    page_text = driver.find_element(By.TAG_NAME, "body").text
    assert "Document uploaded successfully!" in page_text, "File upload failed."
    print("File upload test passed.")

def test_add_revenue():
    """Function to test the revenue addition functionality."""
    driver.get(add_revenue_url)
    time.sleep(2)

    # Enter the revenue amount
    revenue_amount = driver.find_element(By.NAME, "revenue")
    revenue_amount.send_keys("1000")  # Enter a test revenue amount

    # Submit the revenue form
    driver.find_element(By.TAG_NAME, "button").click()
    time.sleep(3)

    # Check if redirected to the taxpayer dashboard
    assert "taxpayer.php" in driver.current_url, "Revenue addition failed - did not redirect to taxpayer dashboard."
    print("Revenue addition test passed.")

try:
    # Run the tests in sequence
    test_signup()
    test_login()
    test_add_revenue()
    #test_upload_file()
    print("All tests completed successfully.")

finally:
    # Close the browser after tests
    driver.quit()
