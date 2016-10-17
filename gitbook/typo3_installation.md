# TYPO3 installation

The following steps are required to active the frontend editing for a TYPO3 installation.

1. Install and active the extension called **frontend_editing**

2. Add the typoscript called **TYPO3 frontend editing** to the site roots where the features should be activated

    [TYPO3 frontend editing typoscript](images/add-typoscript.png)

3. After the inclusion of the typoscript settings there is a need to add the following setting to the site root(s)
    
    Add this to the setup part, where the **1** or **0** indicates if it is active or not
    
    ```
    config.tx_frontend_editing = 1
    ```
    
4. The last thing to do is for the individual users to activate the frontend editing for themselves. This is done in the "User settings" in TYPO3:s backend.

    [User activation of frontend editing](images/user-activation-of-feedit.png)

You are now ready for some easy editing! :)