---
title: Render Farm Overview
tags:
 - internal
---

# Render Farm Overview
This page is to provide a brief overview of what the Render Farm is and to link
additional information. The user manual---documentation for end users on how to
submit and manipulate their jobs---can be found
[here](https://github.rcac.purdue.edu/RCAC-Staff/www/blob/master/knowledge/KB/EngineeringTeam/render-farm/RenderFarmHowTo.pdf).
Due to the concise information provided on this page, maintenance, design
information, and additional documentation links are provided below.
* [Maintaining the Render Farm](https://www.rcac.purdue.edu/knowledge/internal/EngineeringTeam/render-farm/render-farm-maintenance) -
  How to start and stop Qube services, add new worker nodes, etc.

## Render Farm End Users
The Render Farm was commissioned and paid for by the CGT department for use
in the classroom and by graduate students for research. The primary contacts
for CGT and PipelineFX about the Render Farm through RCAC are Laura Theademan
and Stephen Harrell. Render and animation support may be provided by George
Takahashi at the Envision Center. Drew Sumner from CGT (an undergrad student)
is the primary contact for help with Houdini. Additional backend support may
be provided by RCAC Systems.

## Render Farm Physical Infrastructure
The Render Farm hardware currently consists of 5 machines:
* 1 ADM machine ([render-adm.rcac.purdue.edu](http://oculus.rcac.purdue.edu/kickstand/view/host/node=render-adm))
  which is a virtual machine
* 4 physical worker machines (render-a00[0-3].rcac.purdue.edu), ProLiant DL60
  Gen9 machines with 2 Xeon(R) CPU E5-2660v3 processors (40 cores) and 64GB of memory
    * [render-a000](http://oculus.rcac.purdue.edu/kickstand/view/host/node=render-a000)
    * [render-a001](http://oculus.rcac.purdue.edu/kickstand/view/host/node=render-a001)
    * [render-a002](http://oculus.rcac.purdue.edu/kickstand/view/host/node=render-a002)
    * [render-a003](http://oculus.rcac.purdue.edu/kickstand/view/host/node=render-a003)

There was a fifth worker machine, but the CGT department requested it be moved
physically to the CGT department. This fifth machine is _not_ part of the Render
Farm.

## Render Farm Software
The Render Farm uses a package called [Qube! by PipelineFX](https://www.pipelinefx.com)
([Qube! Administrator's Guide](http://docs.pipelinefx.com/display/QUBE/Administrator%27s+Guide)).
Qube uses a scheduler called the _Supervisor_ (running on the ADM machine) to accept,
authenticate, schedule, and execute jobs. Qube uses a _Worker_ (running on the
render-a00[0-3] machines) to render the jobs.

Our environment is set up to support the following rendering software:
* [Mental Ray](https://www.autodesk.com/products/mental-ray-standalone/overview),
  a render engine for the 3D modeling software
  [Maya](https://www.autodesk.com/products/maya/overview)
* [Mantra](https://www.sidefx.com/docs/houdini/render/render), a render engine
  by SideFX for the 3D modeling software [Houdini](https://www.sidefx.com)

We __DO NOT SUPPORT ANY ADDITIONAL RENDER ENGINES__ within the Render Farm
environment.
