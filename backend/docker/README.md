# FabLab on Docker

Everything needed to run FabLab as three containers: `fablab-app` (Apache +
PHP), `fablab-scheduler` and `fablab-db` (MySQL).

**Full guide: [../docs/DockerDeployment.md](../docs/DockerDeployment.md)**

## Quick start (Linux server with Docker and Compose v2)

```bash
cd backend                                    # the folder with artisan in it
unzip /path/to/fablab-gbl.zip -d public/gbl/  # the 3D models: not in git, handed over separately
cd docker
./fablab.sh install              # settings, image, containers, first admin
```

Then open the address it prints. `./fablab.sh help` lists every other command:
`status`, `logs`, `restart`, `update`, `backup`, `restore`, `import-db`,
`create-user` and more.

Settings live in `docker/.env`, which `install` writes from `.env.example`. Apply
changes with `./fablab.sh restart`.
