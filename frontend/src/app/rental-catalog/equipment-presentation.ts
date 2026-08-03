import { EquipmentPresentation } from './rental-pricing.models';

const fallbackPresentation: EquipmentPresentation = {
  imageUrl: '/images/perceuse.webp',
  description: 'Un équipement professionnel entretenu et prêt à accompagner votre chantier.',
  useCase: 'Tous travaux',
};

const presentations: Record<string, EquipmentPresentation> = {
  Perceuse: {
    imageUrl: '/images/perceuse.webp',
    description: 'Compacte et puissante pour le perçage, le vissage et les travaux du quotidien.',
    useCase: 'Percer & visser',
  },
  Ponceuse: {
    imageUrl: '/images/ponceuse.webp',
    description: 'Une finition régulière sur le bois, les meubles et les surfaces à rénover.',
    useCase: 'Poncer & rénover',
  },
  'Scie circulaire': {
    imageUrl: '/images/scie-circulaire.webp',
    description: 'Des coupes droites, rapides et précises pour vos projets de menuiserie.',
    useCase: 'Découper le bois',
  },
  'Nettoyeur haute pression': {
    imageUrl: '/images/nettoyeur-haute-pression.webp',
    description:
      'Retrouvez des extérieurs propres sans effort, de la terrasse au mobilier de jardin.',
    useCase: 'Nettoyer les extérieurs',
  },
  Shampouineuse: {
    imageUrl: '/images/shampouineuse.webp',
    description: 'Un nettoyage en profondeur des tapis, moquettes, canapés et sièges textiles.',
    useCase: 'Nettoyer les textiles',
  },
};

export function presentationFor(equipmentName: string): EquipmentPresentation {
  const normalizedName = equipmentName.toLocaleLowerCase('fr');
  const matchingPresentation = Object.entries(presentations).find(([equipmentType]) =>
    normalizedName.startsWith(equipmentType.toLocaleLowerCase('fr')),
  );

  return matchingPresentation?.[1] ?? fallbackPresentation;
}
