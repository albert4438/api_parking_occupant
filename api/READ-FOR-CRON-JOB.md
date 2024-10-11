Detailed step-by-step guide for setting up the scheduled task for `autoArchiveStudents.php` script using the specified path `D:\xampp\htdocs\parking_occupant\api\autoArchiveStudents.php`.


Step 1: Open Task Scheduler

1. Press `Windows Key + R` to open the Run dialog.
2. Type `taskschd.msc` and press `Enter` to open Task Scheduler.



Step 2: Create a New Task

1. In the Task Scheduler window, click on Create Basic Task in the right-hand panel.
   
2. Name Your Task:
   - In the "Name" field, type Auto Archive Students.
   - You can also add a description, but it's optional.
   - Click Next.



Step 3: Set the Trigger

1. Choose When to Start the Task:
   - Select Daily and click Next.
   
2. Set the Start Date and Time:
   - Choose a start date and set the time you want it to run (e.g., set it to midnight: 12:00:00 AM).
   - Click Next.



Step 4: Set the Action

1. Choose Start a Program:
   - Select Start a program and click Next.
   
2. Set Up the Program to Start:
   - In the Program/script field, browse to the PHP executable. For XAMPP, it’s usually located at:
     ```
     D:\xampp\php\php.exe
     ```
   - In the Add arguments (optional) field, type the full path to your `autoArchiveStudents.php` script:
     ```
     D:\xampp\htdocs\parking_occupant\api\autoArchiveStudents.php
     ```
   - In the Start in (optional) field, type the directory where your script is located (without the script name):
     ```
     D:\xampp\htdocs\parking_occupant\api
     ```
   - The filled-out fields should look like this:
     - Program/script: `D:\xampp\php\php.exe`
     - Add arguments (optional): `D:\xampp\htdocs\parking_occupant\api\autoArchiveStudents.php`
     - Start in (optional): `D:\xampp\htdocs\parking_occupant\api`

3. Click Next.



Step 5: Finish the Task Creation

1. Review the Summary:
   - Make sure everything is correct.
   - Click Finish to create the task.



Step 6: Test the Scheduled Task

1. In Task Scheduler, locate your task Auto Archive Students in the list.
   
2. Run the Task Manually:
   - Right-click on the task and select Run.
   
3. Check Execution History:
   - After running the task, you can click on the History tab to see if it executed successfully.



Step 7: Verify Functionality

1. Check the Database:
   - After running the task, check your database to see if any students have been archived as expected.

 Troubleshooting
- If the task doesn’t seem to run, ensure that:
  - The paths are correct.
  - Your PHP script does not have errors (you can run it manually to test).
  - Task Scheduler is allowed to run tasks with the current user account permissions.

