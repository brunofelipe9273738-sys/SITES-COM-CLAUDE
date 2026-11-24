USE joalheria;
INSERT INTO produtos (categoria_id,nome,slug,descricao,material,peso_gramas,publico,preco,preco_promocional,destaque,lancamento) VALUES
((SELECT id FROM categorias WHERE slug='aneis'),'Anel Solitário Aurora','anel-solitario-aurora','Anel solitário clássico com zircônia central lapidada à mão. Acabamento polido e confortável para uso diário.','Ouro 18k',3.20,'feminino',2890.00,2490.00,1,0),
((SELECT id FROM categorias WHERE slug='aneis'),'Anel Chevalier Black','anel-chevalier-black','Anel masculino robusto com acabamento escovado e detalhe em ônix.','Prata 950 e ônix',8.50,'masculino',1290.00,NULL,0,1),
((SELECT id FROM categorias WHERE slug='aliancas'),'Aliança Eterna Lisa','alianca-eterna-lisa','Aliança tradicional de design atemporal, interior anatômico.','Ouro 18k',4.00,'unissex',1890.00,NULL,1,0),
((SELECT id FROM categorias WHERE slug='colares'),'Colar Ponto de Luz','colar-ponto-de-luz','Corrente veneziana delicada com pingente de ponto de luz.','Ouro 18k',2.10,'feminino',1590.00,1390.00,1,1),
((SELECT id FROM categorias WHERE slug='pulseiras'),'Pulseira Riviera','pulseira-riviera','Pulseira riviera com zircônias em toda a extensão.','Prata 925 banhada a ouro',6.30,'feminino',990.00,NULL,0,1),
((SELECT id FROM categorias WHERE slug='brincos'),'Brinco Argola Cristal','brinco-argola-cristal','Par de argolas médias com cravação em cristais.','Ouro 18k',3.80,'feminino',1190.00,990.00,1,0),
((SELECT id FROM categorias WHERE slug='relogios'),'Relógio Clássico Dourado','relogio-classico-dourado','Relógio de pulso analógico, caixa em aço dourado, resistente à água.','Aço inoxidável',62.00,'unissex',2490.00,NULL,1,1),
((SELECT id FROM categorias WHERE slug='pingentes'),'Pingente Coração','pingente-coracao','Pingente de coração liso, acompanha corrente de 45 cm.','Ouro 18k',1.50,'feminino',690.00,NULL,0,0);

INSERT INTO produto_variacoes (produto_id,rotulo,sku,estoque)
SELECT id,'Aro 14',CONCAT(slug,'-14'),5 FROM produtos WHERE slug IN ('anel-solitario-aurora','anel-chevalier-black','alianca-eterna-lisa');
INSERT INTO produto_variacoes (produto_id,rotulo,sku,estoque)
SELECT id,'Aro 18',CONCAT(slug,'-18'),3 FROM produtos WHERE slug IN ('anel-solitario-aurora','anel-chevalier-black','alianca-eterna-lisa');
INSERT INTO produto_variacoes (produto_id,rotulo,sku,estoque)
SELECT id,'Único',CONCAT(slug,'-un'),8 FROM produtos WHERE slug NOT IN ('anel-solitario-aurora','anel-chevalier-black','alianca-eterna-lisa');
