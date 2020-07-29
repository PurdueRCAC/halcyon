---
title: SSH Keys
tags:
  - linuxcluster
---

# SSH Keys

SSH works with many different means of authentication such as passwords or SSH keys.

To use SSH keys you will need to generate a keypair in the location from where you wish to log in. This keypair consists of two files: private key and public key. You keep the private key file secure on your local computer (hence the name "private" key). You then log in to the remove machine using your password and append the public key to the end of an authorized keys file. In future login attempts, SSH compares the public and private keys to verify your identity instead of prompting for your password.

See the following links for more information on creating SSH keys:

* [Mac OSX and Linux](https://www.digitalocean.com/community/tutorials/how-to-set-up-ssh-keys--2)
* [Windows - MobaXterm](https://cinhtau.net/2016/02/03/use-ssh-keys-for-authentication-with-mobaxterm/)

###Passphrases and SSH Keys

Creating a keypair prompts you to provide a passphrase for the private key. When you create a keypair, you should always provide a corresponding private key passphrase. This passphrase is not recoverable if forgotten, so make note of it. Only a few situations warrant using a non-passphrase-protected private key—conducting automated file backups is one such situation.
