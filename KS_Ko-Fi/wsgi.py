from app import create_app, load_config

# WSGI entrypoint for production servers like gunicorn/uwsgi.
# Example: gunicorn -w 2 -b 127.0.0.1:8787 wsgi:app

app = create_app(load_config())
