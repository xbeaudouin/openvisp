# Be sure to restart your server when you modify this file.

# Your secret key for verifying cookie session data integrity.
# If you change this key, all old sessions will become invalid!
# Make sure the secret is at least 30 characters and all random, 
# no regular words or you'll be exposed to dictionary attacks.
ActionController::Base.session = {
  :key         => '_openvisp_session',
  :secret      => 'e7e4f1d7c01ab586080037b6a21f389928556278cce32281a3b93c4951ae4dd3839b704ea67c4aca9e978e26611bf1b4d4546d8112ee9baad6409e43c5faf0bd'
}

# Use the database for sessions instead of the cookie-based default,
# which shouldn't be used to store highly confidential information
# (create the session table with "rake db:sessions:create")
# ActionController::Base.session_store = :active_record_store
