-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 19, 2024 at 03:58 AM
-- Server version: 5.7.24
-- PHP Version: 8.0.1
create database bdfrappe;
use bdfrappe;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bdfrappe`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `p_catalogos` (IN `im` TEXT, IN `idsa` VARCHAR(45), IN `idca` VARCHAR(45), IN `idpre` VARCHAR(45), IN `id` INT, IN `ev` INT)   begin
	case ev
		when 1 then
			 IF EXISTS (SELECT 1 FROM catalogos WHERE idcatalogo= id) THEN
				 SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '';
			ELSE
				INSERT INTO catalogos(img,fk_idsabor,fk_idcategoria,fk_idpresentacion,cond_cat) values (im,idsa,idca,idpre,0);
                
			END IF;
		 when 2 then
			IF NOT EXISTS (SELECT 1 FROM catalogos WHERE idcatalogo = id) THEN
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '';
			ELSE
				IF im IS NULL OR im = 'undefined' THEN
                    UPDATE catalogos
                    SET fk_idsabor = idsa,
                        fk_idcategoria = idca,
                        fk_idpresentacion = idpre
                    WHERE idcatalogo = id;
                ELSE
                    UPDATE catalogos
                    SET img = im,
                        fk_idsabor = idsa,
                        fk_idcategoria = idca,
                        fk_idpresentacion = idpre
                    WHERE idcatalogo = id;
                END IF;
			END IF;
		when 3 then
			IF EXISTS (SELECT 1 FROM catalogos WHERE idcatalogo= id) THEN
				UPDATE catalogos
				SET cond_cat=1
				WHERE idcatalogo= id;
			ELSE
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El DNI no existe en la tabla personas';
            END IF;
    end case;
end$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `p_compra` (IN `monto` VARCHAR(45), IN `com` VARCHAR(45), IN `tran` VARCHAR(45), IN `idtip` VARCHAR(45), IN `dn` VARCHAR(45), IN `id` INT, IN `ev` INT)   begin
	declare idpro int;
    set idpro=(select  idproveedor from proveedores as p inner join empresas as e on e.idempresa=p.fk_idempresa WHERE fk_dniP=dn limit 1);
	case ev
		when 1 then
            IF NOT EXISTS (SELECT 1 FROM compras WHERE num_comprobanteC=com and fk_idtipo_comprobante=idtip) THEN
                 INSERT INTO compras(fecha_ingresoC, monto_ingresoC, num_comprobanteC, transporte, cond_comp, fk_idtipo_comprobante , fk_idproveedor) values 
                (curdate(), monto, com, tran, 0, idtip, idpro);
			ELSE
				 SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '';
            END IF;
            
		 when 2 then
			IF NOT EXISTS (SELECT 1 FROM compras WHERE idcompra=id) THEN
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '';
			ELSE
				UPDATE compras
                SET monto_ingresoC = monto, num_comprobanteC = com, transporte = tran, fk_idtipo_comprobante = idtip, fk_idproveedor = idpro 
                WHERE idcompra = id;  
			END IF;
		when 3 then
			IF EXISTS (SELECT 1 FROM compras WHERE idcompra=id) THEN
                UPDATE compras
                SET cond_comp = 1
                WHERE idcompra = id;  
			ELSE
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '';
            END IF;
    end case;
end$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `p_detalle_compra` (IN `mont` VARCHAR(45), IN `num` VARCHAR(45), IN `idtip` VARCHAR(45), IN `sto` VARCHAR(15), IN `pre` VARCHAR(45), IN `fech_ven` VARCHAR(45), IN `insu` VARCHAR(45), IN `id` INT, IN `ev` INT)   begin
	declare idin int;
    declare idcom int;
    set idin = (select idinsumo from insumos where nombre_insumo =insu limit 1);
    set idcom = (select idcompra from compras where monto_ingresoC=mont and num_comprobanteC= num and fk_idtipo_comprobante=idtip limit 1);
	case ev
		when 1 then
            IF NOT EXISTS (select 1 from detalle_compras where fk_idcompra=idcom and fk_idinsumo=idin and fecha_ven_insumo=fech_ven and precio_insumo=pre) THEN
				 INSERT INTO detalle_compras(stock_insumo, precio_insumo, fecha_ven_insumo, fk_idinsumo, fk_idcompra) values
                (sto, pre, fech_ven, idin, idcom);
			ELSE
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '';
            END IF;
            
		 when 2 then
			IF NOT EXISTS (SELECT 1 FROM detalle_compras WHERE iddetalle_compra= id) THEN
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '';
			ELSE
				UPDATE detalle_compras
                SET stock_insumo=sto, precio_insumo=pre, fecha_ven_insumo=fech_ven, fk_idinsumo=idin, fk_idcompra=idcom
                WHERE iddetalle_compra=id;
                
			END IF;
		when 3 then
			IF EXISTS (SELECT 1 FROM detalle_compras WHERE iddetalle_compra= id) THEN
                delete from detalle_compras where iddetalle_compra = id ;
			ELSE
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '';
            END IF;
    end case;
end$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `p_detalle_insumos` (IN `insum` VARCHAR(200), IN `produc` VARCHAR(200), IN `cant` VARCHAR(45), IN `ev` INT)   begin
	declare idcat int;
    declare idin int;
    set idcat = (select idcatalogo from catalogos as c inner join categorias as ca on ca.idcategoria=c.fk_idcategoria
				inner join sabores as s on s.idsabor=c.fk_idsabor inner join presentaciones as p on p.idpresentacion=c.fk_idpresentacion
				where CONCAT(categoria, ' - ', sabor, ' (', presentacion, ')') = produc limit 1);
	set idin = (select idinsumo from insumos where nombre_insumo =insum limit 1);
	case ev
		when 1 then
            IF NOT EXISTS (select 1 from detalle_insumos where fk_idcatalogoD=idcat and fk_idinsumoD=idin) THEN
                INSERT INTO detalle_insumos (fk_idcatalogoD,fk_idinsumoD,cantidad_usada) values
                (idcat, idin, cant);
			ELSE
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '';
            END IF;
            
		 when 2 then
			IF NOT EXISTS (select 1 from detalle_insumos where fk_idcatalogoD=idcat and fk_idinsumoD=idin) THEN
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '';
			ELSE
				UPDATE detalle_insumos 
					SET fk_idinsumoD = idin, cantidad_usada = cant
                WHERE fk_idcatalogoD = idcat ;
			END IF;
		when 3 then
			IF EXISTS (select 1 from detalle_insumos where fk_idcatalogoD=idcat and fk_idinsumoD=idin) THEN
                DELETE FROM detalle_insumos WHERE fk_idcatalogoD = idcat AND fk_idinsumoD = idin;
			ELSE
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '';
            END IF;
    end case;
end$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `p_detalle_ventas` (IN `idvent` INT, IN `produc` TEXT, IN `cant` VARCHAR(45), IN `sub` VARCHAR(45))   BEGIN
    DECLARE idpro INT;
    SET idpro = (
        SELECT idproducto FROM productos AS pr 
        INNER JOIN catalogos AS ca ON ca.idcatalogo = pr.fk_idcatalogo INNER JOIN categorias AS c ON c.idcategoria = ca.fk_idcategoria 
        INNER JOIN sabores AS s ON s.idsabor = ca.fk_idsabor INNER JOIN presentaciones AS p ON p.idpresentacion = ca.fk_idpresentacion
        WHERE CONCAT(categoria, ' - ', sabor, ' (', presentacion, ')') LIKE CONCAT('%', produc, '%') LIMIT 1);
        
    INSERT INTO detalle_ventas(fk_idventa, fk_idproducto, cantidad, subtotal)
    VALUES (idvent, idpro, cant, sub);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `p_personales` (IN `dn` VARCHAR(15), IN `ape` VARCHAR(45), IN `nom` VARCHAR(45), IN `tel` VARCHAR(45), IN `rl` INT, IN `ev` INT)   begin
	case ev
		when 1 then
			 IF EXISTS (SELECT 1 FROM personas WHERE dni = dn) THEN
				 UPDATE personas 
					SET apellidos = ape, nombres = nom, telefono = tel, cond=0
					WHERE dni = dn;
			ELSE
				INSERT INTO personas (dni, apellidos, nombres, telefono,cond)
				VALUES (dn, ape, nom, tel,0);
				
				INSERT INTO personales (fk_dniPE, fk_idrol, fecha_ingresoP)
				VALUES (dn, rl, curdate());
			END IF;
		 when 2 then
			IF NOT EXISTS (SELECT 1 FROM personas WHERE dni = dn) THEN
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '';
			ELSE
				UPDATE personas 
				SET apellidos = ape, nombres = nom, telefono = tel
				WHERE dni = dn;
                
				UPDATE personales 
				SET fk_idrol = rl
				WHERE fk_dniPE = dn;
			END IF;
		when 3 then
			IF EXISTS (SELECT 1 FROM personas WHERE dni = dn) THEN
				UPDATE personas
				SET cond=1
				WHERE dni= dn;
			ELSE
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El DNI no existe en la tabla personas';
            END IF;
    end case;
end$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `p_proveedor` (IN `dn` VARCHAR(15), IN `ape` VARCHAR(45), IN `nom` VARCHAR(45), IN `tel` VARCHAR(45), IN `ru` VARCHAR(45), IN `ev` INT)   begin
	declare idempr int;
    set idempr=(select idempresa from empresas where RUC=ru limit 1);
	case ev
		when 1 then
            IF EXISTS (SELECT 1 FROM personas WHERE dni = dn) THEN
				 UPDATE personas 
					SET apellidos = ape, nombres = nom, telefono = tel, cond=0
					WHERE dni = dn;
			ELSE
				INSERT INTO personas(dni,apellidos,nombres,telefono,cond) VALUES (dn,ape,nom,tel,0);
                
				INSERT INTO proveedores(fk_dniP,fk_idempresa) VALUES (dn,idempr);
            END IF;
            
		 when 2 then
			IF NOT EXISTS (SELECT 1 FROM personas WHERE dni = dn) THEN
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '';
			ELSE
				UPDATE personas SET apellidos = ape, nombres = nom, telefono = tel WHERE dni = dn;
				UPDATE proveedores SET fk_idempresa = idempr WHERE fk_dniP = dn;
			END IF;
		when 3 then
			IF EXISTS (SELECT 1 FROM personas WHERE dni = dn) THEN
				UPDATE personas
				SET cond=1
				WHERE dni= dn;
			ELSE
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '';
            END IF;
    end case;
end$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `p_usuarios` (IN `dn` VARCHAR(15), IN `usu` VARCHAR(45), IN `psw` VARCHAR(45), IN `ev` INT)   begin
	case ev
		when 1 then
			 IF EXISTS (SELECT 1 FROM personas WHERE dni = dn) THEN
				 UPDATE personas 
					SET cond=0
					WHERE dni = dn;
                    IF EXISTS (SELECT 1 FROM usuarios WHERE fk_dniU = dn) THEN
						update usuarios set usuario = usu, psswrd = psw
						where fk_dni= dn;
					else
						INSERT INTO usuarios(usuario,psswrd,fk_dniU) values (usu,psw,dn);
                    end if;
			ELSE
				INSERT INTO usuarios(usuario,psswrd,fk_dniU) values (usu,psw,dn);
                
			END IF;
		 when 2 then
			IF NOT EXISTS (SELECT 1 FROM personas WHERE dni = dn) THEN
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = '';
			ELSE
				UPDATE usuarios
				SET usuario = usu, psswrd = psw
				WHERE fk_dniU = dn;
			END IF;
		when 3 then
			IF EXISTS (SELECT 1 FROM personas WHERE dni = dn) THEN
				UPDATE personas
				SET cond=1
				WHERE dni = dn;
			ELSE
				SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El DNI no existe en la tabla personas';
            END IF;
    end case;
end$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `p_ventas` (IN `mont` VARCHAR(200), IN `vuel` VARCHAR(200), IN `mont_let` TEXT, IN `num` VARCHAR(45), IN `idtip` VARCHAR(45), OUT `idvent` INT)   BEGIN
    INSERT INTO ventas(igv, monto_venta, vuelto, monto_letra, fecha_venta, hora_venta)
    VALUES (0.18 * mont, mont, vuel, mont_let, CURDATE(), CURRENT_TIME);
    SET idvent = LAST_INSERT_ID();
    INSERT INTO comprobantes(serie, num_comprobanteV, fk_idventaV, fk_idtipo_comprobanteV)
    VALUES (002, num, idvent, idtip);
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `monto_a_letras` (`monto` DECIMAL(15,2)) RETURNS VARCHAR(255) CHARSET utf8 DETERMINISTIC BEGIN
    DECLARE cantidad INT;
    DECLARE decimales INT;
    DECLARE monto_letras VARCHAR(255);
    DECLARE monto_str VARCHAR(255);
    DECLARE unidades VARCHAR(255);
    DECLARE decimales_texto VARCHAR(20);

    SET monto_str = TRIM(REPLACE(FORMAT(monto, 2), ',', ''));
    SET cantidad = FLOOR(monto);
    SET decimales = ROUND((monto - cantidad) * 100); 
    SET monto_letras = CASE
        WHEN cantidad = 1 THEN 'UNO'
        WHEN cantidad = 2 THEN 'DOS'
        WHEN cantidad = 3 THEN 'TRES'
        WHEN cantidad = 4 THEN 'CUATRO'
        WHEN cantidad = 5 THEN 'CINCO'
        WHEN cantidad = 6 THEN 'SEIS'
        WHEN cantidad = 7 THEN 'SIETE'
        WHEN cantidad = 8 THEN 'OCHO'
        WHEN cantidad = 9 THEN 'NUEVE'
        WHEN cantidad BETWEEN 10 AND 19 THEN CONCAT('DIEZ', CASE
            WHEN cantidad = 10 THEN ''
            WHEN cantidad = 11 THEN ' Y UNO'
            WHEN cantidad = 12 THEN ' Y DOS'
            WHEN cantidad = 13 THEN ' Y TRES'
            WHEN cantidad = 14 THEN ' Y CUATRO'
            WHEN cantidad = 15 THEN ' Y CINCO'
            WHEN cantidad = 16 THEN ' Y SEIS'
            WHEN cantidad = 17 THEN ' Y SIETE'
            WHEN cantidad = 18 THEN ' Y OCHO'
            WHEN cantidad = 19 THEN ' Y NUEVE'
            ELSE ''
        END)
        
        ELSE 'ERROR: SUPERA LA FUNCIONALIDAD'
    END;
    
    SET decimales_texto = CASE
        WHEN decimales = 0 THEN '00'
        WHEN decimales BETWEEN 1 AND 9 THEN CONCAT('0', decimales)
        ELSE CAST(decimales AS CHAR)
    END;
    
    RETURN CONCAT(monto_letras, ' CON ', decimales_texto, '/100 SOLES');
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `catalogos`
--

CREATE TABLE `catalogos` (
  `idcatalogo` int(11) NOT NULL,
  `img` text,
  `cond_cat` int(11) NOT NULL,
  `fk_idsabor` int(11) NOT NULL,
  `fk_idcategoria` int(11) NOT NULL,
  `fk_idpresentacion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `catalogos`
--

INSERT INTO `catalogos` (`idcatalogo`, `img`, `cond_cat`, `fk_idsabor`, `fk_idcategoria`, `fk_idpresentacion`) VALUES
(1, 'frappe_clasico.png', 0, 2, 1, 1),
(2, 'frappe_niños.png', 0, 5, 5, 2),
(3, 'frappe_alcohol.png', 0, 8, 7, 7),
(4, 'frappe_frutal.png', 0, 4, 6, 1),
(5, 'frappe_vegano.png', 0, 3, 8, 2),
(6, 'cafe_expreso.png', 0, 11, 11, 1);

-- --------------------------------------------------------

--
-- Table structure for table `categorias`
--

CREATE TABLE `categorias` (
  `idcategoria` int(11) NOT NULL,
  `categoria` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `categorias`
--

INSERT INTO `categorias` (`idcategoria`, `categoria`) VALUES
(1, 'Frappé Clásico'),
(2, 'Frappé de Temporada'),
(3, 'Frappé Saludable'),
(4, 'Frappé Gourmet'),
(5, 'Frappé para Niños'),
(6, 'Frappé Frutal'),
(7, 'Frappé con Alcohol'),
(8, 'Frappé Vegano'),
(9, 'Frappé de Café Especial'),
(10, 'Frappé Temático'),
(11, 'Cafe');

-- --------------------------------------------------------

--
-- Table structure for table `compras`
--

CREATE TABLE `compras` (
  `idcompra` int(11) NOT NULL,
  `fecha_ingresoC` date NOT NULL,
  `monto_ingresoC` decimal(9,2) NOT NULL,
  `num_comprobanteC` varchar(45) NOT NULL,
  `transporte` double NOT NULL,
  `fk_idtipo_comprobante` int(11) NOT NULL,
  `fk_idproveedor` int(11) NOT NULL,
  `cond_comp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `compras`
--

INSERT INTO `compras` (`idcompra`, `fecha_ingresoC`, `monto_ingresoC`, `num_comprobanteC`, `transporte`, `fk_idtipo_comprobante`, `fk_idproveedor`, `cond_comp`) VALUES
(1, '2024-12-01', '42.00', 'FAC001', 10, 1, 1, 0),
(2, '2024-12-02', '53.00', 'FAC002', 20, 1, 2, 0),
(3, '2024-12-03', '50.00', 'FAC003', 12, 2, 3, 0),
(4, '2024-12-09', '25.00', '120000011349', 10, 1, 3, 0),
(5, '2024-12-09', '36.00', '1200000768321', 12, 1, 2, 0),
(6, '2024-12-09', '180.00', '123456', 12, 1, 3, 0),
(7, '2024-12-09', '144.00', '1212', 12, 1, 1, 0),
(8, '2024-12-09', '20.00', '12344344', 2, 2, 2, 0),
(9, '2024-12-09', '1464.00', '131232142', 30, 2, 3, 0),
(10, '2024-12-09', '24.00', '1222222', 22, 3, 2, 0),
(11, '2024-12-09', '12.00', '12331', 12, 6, 2, 0),
(12, '2024-12-09', '38.00', '1223555455454', 121, 1, 1, 0),
(13, '2024-12-09', '36.00', '212212', 3, 5, 3, 0),
(14, '2024-12-09', '30.00', '12345789', 10, 1, 2, 0);

-- --------------------------------------------------------

--
-- Table structure for table `comprobantes`
--

CREATE TABLE `comprobantes` (
  `idcomprobante` int(11) NOT NULL,
  `serie` varchar(3) NOT NULL,
  `num_comprobanteV` varchar(45) NOT NULL,
  `fk_idventaV` int(11) NOT NULL,
  `fk_idusuarioV` int(11) DEFAULT NULL,
  `fk_idtipo_comprobanteV` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `comprobantes`
--

INSERT INTO `comprobantes` (`idcomprobante`, `serie`, `num_comprobanteV`, `fk_idventaV`, `fk_idusuarioV`, `fk_idtipo_comprobanteV`) VALUES
(1, '2', '000000000001', 4, NULL, 2),
(2, '2', '000000000004', 5, NULL, 2),
(3, '2', '000000000001', 6, NULL, 1),
(4, '2', '000000000002', 7, NULL, 1),
(5, '2', '000000000003', 8, NULL, 2),
(6, '2', '000000000002', 9, NULL, 2),
(7, '2', '000000000003', 10, NULL, 1),
(8, '2', '000000000005', 11, NULL, 2),
(9, '2', '000000000004', 12, NULL, 1),
(10, '2', '000000000006', 13, NULL, 2),
(11, '2', '000000000001', 14, NULL, 5),
(12, '2', '000000000005', 15, NULL, 1),
(13, '2', '000000000007', 16, NULL, 2),
(14, '2', '000000000001', 17, NULL, 3),
(15, '2', '000000000002', 18, NULL, 3),
(16, '2', '000000000006', 19, NULL, 1),
(17, '2', '000000000008', 20, NULL, 2),
(18, '2', '000000000009', 21, NULL, 2),
(19, '2', '000000000001', 22, NULL, 4),
(20, '2', '000000000010', 23, NULL, 2),
(21, '2', '000000000011', 24, NULL, 2),
(22, '2', '000000000002', 25, NULL, 4),
(23, '2', '000000000003', 26, NULL, 4),
(24, '2', '000000000004', 27, NULL, 4);

-- --------------------------------------------------------

--
-- Table structure for table `detalle_compras`
--

CREATE TABLE `detalle_compras` (
  `iddetalle_compra` int(11) NOT NULL,
  `stock_insumo` double NOT NULL,
  `precio_insumo` decimal(9,2) NOT NULL,
  `fecha_ven_insumo` date NOT NULL,
  `fk_idinsumo` int(11) NOT NULL,
  `fk_idcompra` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `detalle_compras`
--

INSERT INTO `detalle_compras` (`iddetalle_compra`, `stock_insumo`, `precio_insumo`, `fecha_ven_insumo`, `fk_idinsumo`, `fk_idcompra`) VALUES
(1, 10, '3.20', '2025-06-01', 1, 1),
(2, 20, '0.50', '2025-06-01', 2, 1),
(3, 10, '5.00', '2025-06-01', 3, 2),
(4, 10, '0.30', '2025-06-01', 4, 2),
(5, 20, '2.50', '2025-06-01', 5, 3),
(6, 10, '2.50', '2024-12-30', 5, 4),
(7, 12, '3.00', '2024-12-26', 3, 13),
(8, 5, '2.00', '2025-01-05', 1, 14),
(9, 20, '1.00', '2024-12-25', 7, 14);

-- --------------------------------------------------------

--
-- Table structure for table `detalle_insumos`
--

CREATE TABLE `detalle_insumos` (
  `fk_idcatalogoD` int(11) NOT NULL,
  `fk_idinsumoD` int(11) NOT NULL,
  `cantidad_usada` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `detalle_insumos`
--

INSERT INTO `detalle_insumos` (`fk_idcatalogoD`, `fk_idinsumoD`, `cantidad_usada`) VALUES
(3, 1, 0.12),
(3, 2, 0.4),
(3, 4, 0.31),
(3, 5, 0.23),
(4, 1, 0.5),
(4, 2, 0.12),
(4, 9, 0.3),
(4, 11, 0.4),
(5, 1, 0.01),
(5, 2, 0.3),
(5, 8, 0.02),
(5, 14, 0.6),
(6, 1, 0.06),
(6, 3, 0.03),
(6, 10, 0.03),
(6, 11, 0),
(6, 12, 0.02);

-- --------------------------------------------------------

--
-- Table structure for table `detalle_ventas`
--

CREATE TABLE `detalle_ventas` (
  `fk_idventa` int(11) NOT NULL,
  `fk_idproducto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `subtotal` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `detalle_ventas`
--

INSERT INTO `detalle_ventas` (`fk_idventa`, `fk_idproducto`, `cantidad`, `subtotal`) VALUES
(1, 1, 2, '50.00'),
(1, 2, 3, '45.00'),
(1, 3, 1, '23.00'),
(2, 1, 2, '46.00'),
(2, 2, 5, '100.00'),
(2, 3, 4, '90.00'),
(3, 1, 1, '23.00'),
(3, 2, 3, '69.00'),
(3, 3, 2, '46.00'),
(3, 4, 1, '23.00'),
(3, 5, 1, '23.00'),
(10, 1, 1, '20'),
(18, 1, 2, '31.00'),
(19, 2, 2, '40.00'),
(19, 5, 2, '51.00'),
(20, 2, 2, '40.00'),
(20, 3, 1, '10.75'),
(21, 2, 2, '40.00'),
(22, 2, 3, '60.00'),
(22, 3, 12, '129.00'),
(23, 2, 2, '40.00'),
(23, 3, 8, '86.00'),
(23, 5, 3, '76.50'),
(24, 3, 12, '129.00'),
(25, 1, 2, '31.00'),
(26, 2, 2, '40.00'),
(27, 2, 2, '40.00');

-- --------------------------------------------------------

--
-- Table structure for table `empresas`
--

CREATE TABLE `empresas` (
  `idempresa` int(11) NOT NULL,
  `nombre_empresa` varchar(100) NOT NULL,
  `RUC` varchar(15) NOT NULL,
  `direccion` varchar(100) NOT NULL,
  `telefono_em` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `empresas`
--

INSERT INTO `empresas` (`idempresa`, `nombre_empresa`, `RUC`, `direccion`, `telefono_em`) VALUES
(1, 'Coca Cola S.A', '20456789012', 'Av. Principal 123', '987123456'),
(2, 'Trujillo S.A.C', '20456789013', 'Av. Secundaria 456', '987123457'),
(3, 'Cielo S.A', '20456789014', 'Av. Tercera 789', '987123458');

-- --------------------------------------------------------

--
-- Table structure for table `insumos`
--

CREATE TABLE `insumos` (
  `idinsumo` int(11) NOT NULL,
  `nombre_insumo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `insumos`
--

INSERT INTO `insumos` (`idinsumo`, `nombre_insumo`) VALUES
(1, 'Azucar'),
(2, 'Hielo'),
(3, 'Cafe'),
(4, 'Crema Batida'),
(5, 'Alcohol'),
(7, 'Cicle'),
(8, 'Leche condensada'),
(9, 'Cacao en Polvo'),
(10, 'Canela'),
(11, 'Crema'),
(12, 'Leche'),
(13, 'Chocolate en Polvo'),
(14, 'Frutas');

-- --------------------------------------------------------

--
-- Table structure for table `pagos`
--

CREATE TABLE `pagos` (
  `idpago` int(11) NOT NULL,
  `monto_pagoP` varchar(45) NOT NULL,
  `fecha_pagoP` date NOT NULL,
  `fk_idpersonal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `personales`
--

CREATE TABLE `personales` (
  `idpersonal` int(11) NOT NULL,
  `fecha_ingresoP` date NOT NULL,
  `fk_dniPE` varchar(8) NOT NULL,
  `fk_idrol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `personales`
--

INSERT INTO `personales` (`idpersonal`, `fecha_ingresoP`, `fk_dniPE`, `fk_idrol`) VALUES
(1, '2024-11-30', '72345678', 3),
(2, '2024-11-30', '72345677', 1),
(3, '2024-11-30', '76160748', 2),
(4, '2024-11-30', '76160749', 1),
(5, '2024-12-01', '12121221', 3),
(6, '2024-12-09', '2112345', 5),
(7, '2024-12-09', '3323', 3);

-- --------------------------------------------------------

--
-- Table structure for table `personas`
--

CREATE TABLE `personas` (
  `dni` varchar(8) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `cond` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `personas`
--

INSERT INTO `personas` (`dni`, `apellidos`, `nombres`, `telefono`, `cond`) VALUES
('12121221', 'huanuco li', 'ssss', '974345567', 1),
('12345670', 'Pérez López', 'Juan', '987654321', 0),
('12345678', 'Gonzales Perez', 'Juan Carlos', '987654321', 0),
('2112345', 'asas', 'qqqq', '1232', 1),
('23456789', 'Lopez Martinez', 'Ana Maria', '912345678', 0),
('3323', 'qwq', 'wwqwqw', '121121', 1),
('33321455', 'Macario Luna', 'Angel Luis', '950034567', 0),
('34567890', 'Ramirez Torres', 'Luis Fernando', '956789012', 0),
('45678901', 'Sanchez Vega', 'Carmen Julia', '978901234', 0),
('563', 'we qq', 'aaaa', '98765665555', 1),
('56781230', 'Ramírez Soto', 'Carlos', '987654323', 0),
('56789012', 'Diaz Salazar', 'Pedro Antonio', '987123456', 0),
('67890123', 'Fernandez Rios', 'Claudia Patricia', '912678345', 0),
('72345677', 'Perez Martinez', 'Juan', '987654321', 0),
('72345678', 'Perez Luna', 'Carlos', '987654320', 0),
('76160748', 'gonzales huaromo', 'yelsen', '910706967', 1),
('76160749', 'gonzales huaromo', 'yelsen', '910706967', 1),
('87654320', 'Gómez Torres', 'María', '987654322', 0);

-- --------------------------------------------------------

--
-- Table structure for table `presentaciones`
--

CREATE TABLE `presentaciones` (
  `idpresentacion` int(11) NOT NULL,
  `presentacion` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `presentaciones`
--

INSERT INTO `presentaciones` (`idpresentacion`, `presentacion`) VALUES
(1, 'Vaso Pequeño 12oz'),
(2, 'Vaso Mediano 16oz'),
(3, 'Vaso Grande 20oz'),
(4, 'Vaso Extra Grande 24oz'),
(5, 'Botella de Vidrio 500ml'),
(6, 'Botella de Plástico 600ml'),
(7, 'Empaque para Llevar 1L'),
(8, 'Empaque para Llevar 2L'),
(9, 'Combo Familiar 4 Vasos Grandes'),
(10, 'Edición Especial con Logo Personalizado'),
(12, 'Taza');

-- --------------------------------------------------------

--
-- Table structure for table `productos`
--

CREATE TABLE `productos` (
  `idproducto` int(11) NOT NULL,
  `stock_producto` int(11) NOT NULL,
  `precio_venta` decimal(9,2) NOT NULL,
  `fecha_entrada` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `fk_idcatalogo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `productos`
--

INSERT INTO `productos` (`idproducto`, `stock_producto`, `precio_venta`, `fecha_entrada`, `fecha_vencimiento`, `fk_idcatalogo`) VALUES
(1, 10, '15.50', '2024-12-01', '2025-12-01', 1),
(2, 5, '20.00', '2024-12-02', '2025-12-15', 2),
(3, 20, '10.75', '2024-12-03', '2025-12-10', 3),
(4, 5, '18.99', '2024-12-04', '2025-12-30', 4),
(5, 7, '25.50', '2024-12-05', '2025-12-20', 5);

-- --------------------------------------------------------

--
-- Table structure for table `proveedores`
--

CREATE TABLE `proveedores` (
  `idproveedor` int(11) NOT NULL,
  `fk_dniP` varchar(8) NOT NULL,
  `fk_idempresa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `proveedores`
--

INSERT INTO `proveedores` (`idproveedor`, `fk_dniP`, `fk_idempresa`) VALUES
(1, '12345670', 1),
(2, '87654320', 2),
(3, '56781230', 3),
(4, '563', 1),
(5, '33321455', 3);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `idrol` int(11) NOT NULL,
  `nombre_rol` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`idrol`, `nombre_rol`) VALUES
(1, 'Administrador General'),
(2, 'Barista'),
(3, 'Cajero'),
(4, 'Encargado de Inventario'),
(5, 'Supervisor de Tienda');

-- --------------------------------------------------------

--
-- Table structure for table `sabores`
--

CREATE TABLE `sabores` (
  `idsabor` int(11) NOT NULL,
  `sabor` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `sabores`
--

INSERT INTO `sabores` (`idsabor`, `sabor`) VALUES
(1, 'Vainilla'),
(2, 'Chocolate'),
(3, 'Fresa'),
(4, 'Caramelo'),
(5, 'Café Mocha'),
(6, 'Matcha'),
(7, 'Cookies and Cream'),
(8, 'Mango Tropical'),
(9, 'Avellana'),
(10, 'Coco'),
(11, 'Expresso'),
(12, 'Cold Brew'),
(13, 'Filtrado');

-- --------------------------------------------------------

--
-- Table structure for table `tipo_comprobantes`
--

CREATE TABLE `tipo_comprobantes` (
  `idtipo_comprobante` int(11) NOT NULL,
  `tipo_comprobante` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tipo_comprobantes`
--

INSERT INTO `tipo_comprobantes` (`idtipo_comprobante`, `tipo_comprobante`) VALUES
(1, 'Factura'),
(2, 'Boleta'),
(3, 'Nota de Crédito'),
(4, 'Nota de Débito'),
(5, 'Guía de Remisión'),
(6, 'Recibo por Honorarios'),
(7, 'Otros');

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `idusuario` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `psswrd` text NOT NULL,
  `fk_dniU` varchar(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`idusuario`, `usuario`, `psswrd`, `fk_dniU`) VALUES
(1, 'jcarlos', 'password123', '12345678'),
(2, 'amaria', 'securepass', '23456789'),
(3, 'admin', 'admin', '34567890'),
(4, 'cjulia', 'pass789', '45678901'),
(5, 'pantonio', 'admin123', '56789012'),
(6, 'cpatricia', 'claudia321', '67890123'),
(7, 'BB', 'admin', '76160748'),
(8, '11', '22', '76160749');

-- --------------------------------------------------------

--
-- Table structure for table `ventas`
--

CREATE TABLE `ventas` (
  `idventa` int(11) NOT NULL,
  `igv` decimal(9,2) NOT NULL,
  `monto_venta` decimal(9,2) NOT NULL,
  `vuelto` decimal(9,2) NOT NULL,
  `monto_letra` text NOT NULL,
  `fecha_venta` date NOT NULL,
  `hora_venta` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ventas`
--

INSERT INTO `ventas` (`idventa`, `igv`, `monto_venta`, `vuelto`, `monto_letra`, `fecha_venta`, `hora_venta`) VALUES
(1, '18.00', '118.00', '0.00', '118CIENTOS  CON 00/100 SOLES', '2024-12-01', '10:30:00'),
(2, '36.00', '236.00', '5.00', '236CIENTOS  CON 00/100 SOLES', '2024-12-02', '15:45:00'),
(3, '54.00', '354.00', '10.00', '354CIENTOS  CON 00/100 SOLES', '2024-12-03', '18:00:00'),
(4, '21.60', '120.00', '30.00', 'siento vaeinte 3 mid dosirntos 100/00 soles', '2024-12-17', '15:54:21'),
(5, '22.32', '124.00', '26.00', 'siento vaeinte 3 mid dosirntos 100/00 soles', '2024-12-17', '15:56:48'),
(6, '9.18', '51.00', '4.00', 'ninguno', '2024-12-17', '17:54:44'),
(7, '26.45', '146.96', '3.04', 'ninguno', '2024-12-17', '17:56:36'),
(8, '3.60', '20.00', '0.00', 'ninguno', '2024-12-17', '17:58:15'),
(9, '3.60', '20.00', '0.00', 'ninguno', '2024-12-17', '17:59:44'),
(10, '7.20', '40.00', '0.00', 'ninguno', '2024-12-17', '18:08:04'),
(11, '13.95', '77.50', '2.50', 'ninguno', '2024-12-17', '18:14:55'),
(12, '3.87', '21.50', '8.50', 'ninguno', '2024-12-17', '18:16:38'),
(13, '5.58', '31.00', '9.00', 'ninguno', '2024-12-17', '18:19:17'),
(14, '10.80', '60.00', '0.00', 'ninguno', '2024-12-17', '18:22:15'),
(15, '5.58', '31.00', '4.00', 'ninguno', '2024-12-17', '18:37:45'),
(16, '5.58', '31.00', '4.00', 'ninguno', '2024-12-17', '18:39:16'),
(17, '3.60', '20.00', '0.00', 'ninguno', '2024-12-17', '18:40:55'),
(18, '5.58', '31.00', '4.00', 'ninguno', '2024-12-17', '18:41:43'),
(19, '16.38', '91.00', '9.00', 'ninguno', '2024-12-17', '18:45:48'),
(20, '9.14', '50.75', '9.25', 'Cincuenta con 75/100', '2024-12-17', '18:50:21'),
(21, '7.20', '40.00', '0.00', 'Cuarenta y 00/100 SOLES', '2024-12-17', '18:54:46'),
(22, '34.02', '189.00', '11.00', 'Cien ochenta y nueve y 00/100 SOLES', '2024-12-17', '18:55:13'),
(23, '36.45', '202.50', '7.50', 'DOSCIENTOS DOS Y 50/100 SOLES', '2024-12-17', '18:57:47'),
(24, '23.22', '129.00', '21.00', 'CIEN VEINTE Y NUEVE Y 00/100 SOLES', '2024-12-17', '19:25:00'),
(25, '5.58', '31.00', '9.00', 'TREINTA Y UNO Y 00/100 SOLES', '2024-12-17', '19:26:26'),
(26, '7.20', '40.00', '10.00', 'CUARENTA Y 00/100 SOLES', '2024-12-17', '19:27:37'),
(27, '7.20', '40.00', '10.00', 'CUARENTA Y 00/100 SOLES', '2024-12-17', '19:31:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `catalogos`
--
ALTER TABLE `catalogos`
  ADD PRIMARY KEY (`idcatalogo`),
  ADD KEY `fk_idsabor` (`fk_idsabor`),
  ADD KEY `fk_idcategoria` (`fk_idcategoria`),
  ADD KEY `fk_idpresentacion` (`fk_idpresentacion`);

--
-- Indexes for table `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`idcategoria`);

--
-- Indexes for table `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`idcompra`),
  ADD KEY `fk_idtipo_comprobante` (`fk_idtipo_comprobante`),
  ADD KEY `fk_idproveedor` (`fk_idproveedor`);

--
-- Indexes for table `comprobantes`
--
ALTER TABLE `comprobantes`
  ADD PRIMARY KEY (`idcomprobante`),
  ADD KEY `fk_idventaV` (`fk_idventaV`),
  ADD KEY `fk_idtipo_comprobanteV` (`fk_idtipo_comprobanteV`),
  ADD KEY `comprobantes_ibfk_2` (`fk_idusuarioV`);

--
-- Indexes for table `detalle_compras`
--
ALTER TABLE `detalle_compras`
  ADD PRIMARY KEY (`iddetalle_compra`),
  ADD KEY `fk_idinsumo` (`fk_idinsumo`),
  ADD KEY `fk_idcompra` (`fk_idcompra`);

--
-- Indexes for table `detalle_insumos`
--
ALTER TABLE `detalle_insumos`
  ADD PRIMARY KEY (`fk_idcatalogoD`,`fk_idinsumoD`),
  ADD KEY `fk_idinsumoD` (`fk_idinsumoD`);

--
-- Indexes for table `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD PRIMARY KEY (`fk_idventa`,`fk_idproducto`),
  ADD KEY `fk_idproducto` (`fk_idproducto`);

--
-- Indexes for table `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`idempresa`);

--
-- Indexes for table `insumos`
--
ALTER TABLE `insumos`
  ADD PRIMARY KEY (`idinsumo`);

--
-- Indexes for table `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`idpago`),
  ADD KEY `fk_idpersonal` (`fk_idpersonal`);

--
-- Indexes for table `personales`
--
ALTER TABLE `personales`
  ADD PRIMARY KEY (`idpersonal`),
  ADD KEY `fk_dniPE` (`fk_dniPE`),
  ADD KEY `fk_idrol` (`fk_idrol`);

--
-- Indexes for table `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`dni`),
  ADD UNIQUE KEY `dni` (`dni`);

--
-- Indexes for table `presentaciones`
--
ALTER TABLE `presentaciones`
  ADD PRIMARY KEY (`idpresentacion`);

--
-- Indexes for table `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`idproducto`),
  ADD KEY `fk_idcatalogo` (`fk_idcatalogo`);

--
-- Indexes for table `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`idproveedor`),
  ADD KEY `fk_idempresa` (`fk_idempresa`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`idrol`);

--
-- Indexes for table `sabores`
--
ALTER TABLE `sabores`
  ADD PRIMARY KEY (`idsabor`);

--
-- Indexes for table `tipo_comprobantes`
--
ALTER TABLE `tipo_comprobantes`
  ADD PRIMARY KEY (`idtipo_comprobante`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`idusuario`),
  ADD KEY `fk_dniU` (`fk_dniU`);

--
-- Indexes for table `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`idventa`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `catalogos`
--
ALTER TABLE `catalogos`
  MODIFY `idcatalogo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categorias`
--
ALTER TABLE `categorias`
  MODIFY `idcategoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `compras`
--
ALTER TABLE `compras`
  MODIFY `idcompra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `comprobantes`
--
ALTER TABLE `comprobantes`
  MODIFY `idcomprobante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `detalle_compras`
--
ALTER TABLE `detalle_compras`
  MODIFY `iddetalle_compra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `empresas`
--
ALTER TABLE `empresas`
  MODIFY `idempresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `insumos`
--
ALTER TABLE `insumos`
  MODIFY `idinsumo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `pagos`
--
ALTER TABLE `pagos`
  MODIFY `idpago` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personales`
--
ALTER TABLE `personales`
  MODIFY `idpersonal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `presentaciones`
--
ALTER TABLE `presentaciones`
  MODIFY `idpresentacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `productos`
--
ALTER TABLE `productos`
  MODIFY `idproducto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `idproveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `idrol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `sabores`
--
ALTER TABLE `sabores`
  MODIFY `idsabor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tipo_comprobantes`
--
ALTER TABLE `tipo_comprobantes`
  MODIFY `idtipo_comprobante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `idusuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ventas`
--
ALTER TABLE `ventas`
  MODIFY `idventa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `catalogos`
--
ALTER TABLE `catalogos`
  ADD CONSTRAINT `catalogos_ibfk_1` FOREIGN KEY (`fk_idsabor`) REFERENCES `sabores` (`idsabor`),
  ADD CONSTRAINT `catalogos_ibfk_2` FOREIGN KEY (`fk_idcategoria`) REFERENCES `categorias` (`idcategoria`),
  ADD CONSTRAINT `catalogos_ibfk_3` FOREIGN KEY (`fk_idpresentacion`) REFERENCES `presentaciones` (`idpresentacion`);

--
-- Constraints for table `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`fk_idtipo_comprobante`) REFERENCES `tipo_comprobantes` (`idtipo_comprobante`),
  ADD CONSTRAINT `compras_ibfk_2` FOREIGN KEY (`fk_idproveedor`) REFERENCES `proveedores` (`idproveedor`);

--
-- Constraints for table `comprobantes`
--
ALTER TABLE `comprobantes`
  ADD CONSTRAINT `comprobantes_ibfk_1` FOREIGN KEY (`fk_idventaV`) REFERENCES `ventas` (`idventa`),
  ADD CONSTRAINT `comprobantes_ibfk_2` FOREIGN KEY (`fk_idusuarioV`) REFERENCES `usuarios` (`idusuario`),
  ADD CONSTRAINT `comprobantes_ibfk_3` FOREIGN KEY (`fk_idtipo_comprobanteV`) REFERENCES `tipo_comprobantes` (`idtipo_comprobante`);

--
-- Constraints for table `detalle_compras`
--
ALTER TABLE `detalle_compras`
  ADD CONSTRAINT `detalle_compras_ibfk_1` FOREIGN KEY (`fk_idinsumo`) REFERENCES `insumos` (`idinsumo`),
  ADD CONSTRAINT `detalle_compras_ibfk_2` FOREIGN KEY (`fk_idcompra`) REFERENCES `compras` (`idcompra`);

--
-- Constraints for table `detalle_insumos`
--
ALTER TABLE `detalle_insumos`
  ADD CONSTRAINT `detalle_insumos_ibfk_1` FOREIGN KEY (`fk_idcatalogoD`) REFERENCES `catalogos` (`idcatalogo`),
  ADD CONSTRAINT `detalle_insumos_ibfk_2` FOREIGN KEY (`fk_idinsumoD`) REFERENCES `insumos` (`idinsumo`);

--
-- Constraints for table `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD CONSTRAINT `detalle_ventas_ibfk_1` FOREIGN KEY (`fk_idventa`) REFERENCES `ventas` (`idventa`),
  ADD CONSTRAINT `detalle_ventas_ibfk_2` FOREIGN KEY (`fk_idproducto`) REFERENCES `productos` (`idproducto`);

--
-- Constraints for table `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `pagos_ibfk_1` FOREIGN KEY (`fk_idpersonal`) REFERENCES `personales` (`idpersonal`);

--
-- Constraints for table `personales`
--
ALTER TABLE `personales`
  ADD CONSTRAINT `personales_ibfk_1` FOREIGN KEY (`fk_dniPE`) REFERENCES `personas` (`dni`),
  ADD CONSTRAINT `personales_ibfk_2` FOREIGN KEY (`fk_idrol`) REFERENCES `roles` (`idrol`);

--
-- Constraints for table `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`fk_idcatalogo`) REFERENCES `catalogos` (`idcatalogo`);

--
-- Constraints for table `proveedores`
--
ALTER TABLE `proveedores`
  ADD CONSTRAINT `proveedores_ibfk_1` FOREIGN KEY (`fk_idempresa`) REFERENCES `empresas` (`idempresa`);

--
-- Constraints for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`fk_dniU`) REFERENCES `personas` (`dni`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
