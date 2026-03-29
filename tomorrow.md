 Gemini said

Since you've already tackled the heavy lifting by setting up the Android SDK, JDK, and command-line tools, installing Flutter itself is actually the easiest part.

Because Flutter acts as its own package manager and frequently updates itself, the cleanest way to install it on an Arch-based distro like Archcraft—especially without Android Studio—is to bypass the AUR entirely and clone it directly from GitHub into your home directory. This avoids the annoying permission issues that can happen with system-wide /opt/ installations.

Here is exactly how to finish your setup.
Step 1: Clone the Flutter SDK

Open your terminal and clone the stable branch of Flutter into a hidden directory in your home folder (we'll use ~/.flutter-sdk for this example):
Bash

git clone https://github.com/flutter/flutter.git -b stable ~/.flutter-sdk

Step 2: Add Flutter to your PATH

You need to tell your system where the Flutter command is located. Archcraft uses zsh as its default shell, so you'll want to add it to your ~/.zshrc file.

(Note: If you are using bash, replace .zshrc with .bashrc in the commands below).
Bash

echo 'export PATH="$PATH:$HOME/.flutter-sdk/bin"' >> ~/.zshrc
source ~/.zshrc

Step 3: Tell Flutter where your Android SDK is

Since you didn't install Android Studio to set up the default paths, you need to explicitly point Flutter to your Android SDK installation.

If you installed the SDK via the Arch User Repository (AUR), it is likely in /opt/android-sdk. If you downloaded it manually, it might be somewhere else like ~/Android/Sdk.

Run this command, replacing the path with your actual SDK location:
Bash

flutter config --android-sdk /opt/android-sdk

(Tip: If you aren't sure where it is, check where your ANDROID_HOME or ANDROID_SDK_ROOT environment variable is pointing).
Step 4: Accept Android Licenses

Flutter requires you to accept the Android SDK licenses before it will compile apps. Since you already have the cmdline-tools installed, this should run smoothly:
Bash

flutter doctor --android-licenses

Press y and Enter for each prompt until you've accepted them all.
Step 5: Run Flutter Doctor

Finally, run the diagnostic tool to make sure everything is connected properly:
Bash

flutter doctor

What to expect in the output:

    Android toolchain: This should have a green checkmark [✓].

    Android Studio: This will show an [!] or [✗] saying Android Studio is not installed. You can completely ignore this warning. As long as the "Android toolchain" has a green checkmark, you have everything you need to build Android apps from the command line or using a lightweight editor like VS Code or Neovim.

Would you like some recommendations on setting up VS Code or Neovim for Flutter development now that your core tools are installed?