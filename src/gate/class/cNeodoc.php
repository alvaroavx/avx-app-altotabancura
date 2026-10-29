<?php
/**
 * Neodoc
 * Clase que maneja los Neodocs
 */
class Neodoc extends Wiss
{
    /**
     * Get
     * Obtiene Neodoc segun el IdRecurso
     * 1 Neodoc
     * 2 Categoria
     * 3 Etiqueta
     * 4 Lista
     * @param int $IdRecurso Identificador del recurso
     * @return array
     */
    public function Get($IdRecurso)
    {
        $Datos['IdRecurso'] = $IdRecurso;
        $Datos['IdUsuario'] = Sesion('idusuario');
        return Wiss::Query('spRec_Neodoc_Neodoc', $Datos);
    }

    /**
     * Save
     * Crea y/o actualiza un Neodoc
     * @param $IdRecurso
     * @param $IdCategoria
     * @param $Titulo
     * @param $Bajada
     * @param $Contenido
     * @return array
     */
    public function Save($IdRecurso, $IdCategoria, $Titulo, $Bajada, $Contenido)
    {
        $Datos['IdRecurso']     = $IdRecurso;
        $Datos['IdUsuario']     = Sesion('idusuario');
        $Datos['IdCategoria']   = $IdCategoria;
        $Datos['Titulo']        = $Titulo;
        $Datos['Bajada']        = $Bajada;
        $Datos['Contenido']     = $Contenido;
        return Wiss::Query('spIns_Recurso_Neodoc', $Datos);
    }

    /**
     * Delete
     * Elimina un Neodoc con su Id
     * @param $IdRecurso
     * @return array
     */
    public function Delete($IdRecurso) {
        $Datos['IdUsuario'] = Sesion('idusuario');
        $Datos['IdRecurso'] = $IdRecurso;
        return Wiss::Query('spDel_Recurso_Neodoc', $Datos);
    }

    /**
     * Recupera un Neodoc eliminado
     * @param $IdRecurso
     * @return mixed
     */
    public function Restore($IdRecurso) {
        $Datos['IdUsuario'] = Sesion('idusuario');
        $Datos['IdRecurso'] = $IdRecurso;
        return Wiss::Query('spRec_Neodoc_Restaura', $Datos);
    }

    /**
     * Agrega o elimina un Neodoc de los favoritos
     * @param $IdRecurso
     * @return mixed
     */
    public function Star($IdRecurso)
    {
        $Datos['IdRecurso'] = $IdRecurso;
        $Datos['IdUsuario'] = Sesion('idusuario');
        return Wiss::Query('spRec_Actividad_Favorito', $Datos);
    }


    public function Clone($IdRecurso)
    {
        $Datos['IdRecurso'] = $IdRecurso;
        $Datos['IdUsuario'] = Sesion('idusuario');
        return Wiss::Query('spRec_Neodoc_Clona', $Datos);
    }

    /**
     * Devuelve los Neodocs favoritos del usuario
     * @param int $Top
     * @return mixed
     */
    public function getFavoritos($Top = 0)
    {
        $Datos['IdUsuario'] = Sesion('idusuario');
        $Datos['Top'] = $Top;
        return Wiss::Query('spRec_Actividad_Favorito', $Datos);
    }

    /**
     * Obtiene los ultimos modificados segun el usuario ordenados por fecha
     * @param $Max
     * @return mixed
     */
    public function getRecientes($Max) {
        $Datos['IdUsuario'] = Sesion('idusuario');
        $Datos['Max'] = $Max;
        return Wiss::Query('spRec_Neodoc_Recientes', $Datos);
    }

    /**
     * Obtiene los neodocs eliminados
     * @return mixed
     */
    public function getEliminados() {
        $Datos['IdUsuario'] = Sesion('idusuario');
        return Wiss::Query('spRec_Neodoc_Eliminados', $Datos);
    }

    /**
     * Obtiene los Neodocs por Etiqueta
     * @param $Etiqueta
     * @return mixed
     */
    public function getByEtiqueta($Etiqueta) {
        $Datos['IdUsuario'] = Sesion('idusuario');
        $Datos['Etiqueta'] = $Etiqueta;
        return Wiss::Query('spRec_Neodoc_Etiqueta', $Datos);
    }

    /**
     * Obtiene los Neodocs por Categoria
     * @param $IdCategoria
     * @return mixed
     */
    public function getByCategoria($IdCategoria) {
        $Datos['IdUsuario'] = Sesion('idusuario');
        $Datos['IdCategoria'] = $IdCategoria;
        return Wiss::Query('spRec_Neodoc_Categoria', $Datos);
    }
}